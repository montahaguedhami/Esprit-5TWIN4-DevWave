<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaterQualityController extends Controller
{
    public function __construct(private readonly WaterQualityService $waterQualityService) {}

    public function points(): JsonResponse
    {
        $user = $this->resolveApiUser($request);
        if ($user && ! $user->hasPermission('measurements.view')) {
            return response()->json(['message' => 'Accès refusé : permission measurements.view requise.'], 403);
        }

        $query = PointMesure::with('infrastructure.zone');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%");
            });
        }

        if ($statut = $request->input('statut')) {
            if (in_array($statut, ['actif', 'inactif'], true)) {
                $query->where('statut', $statut);
            }
        } else {
            $query->where('statut', 'actif');
        }

        return response()->json([
            'data' => PointMesure::query()
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveApiUser($request);
        if ($user && ! $user->hasPermission('measurements.view')) {
            return response()->json(['message' => 'Accès refusé : permission measurements.view requise.'], 403);
        }

        $query = MesureQualite::with('pointMesure.infrastructure')->latest('date_mesure');

        if ($request->filled('point_mesure_id')) {
            $query->where('point_mesure_id', $request->integer('point_mesure_id'));
        }

        return response()->json([
            'data' => $query->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function show(MesureQualite $mesure): JsonResponse
    {
        $user = $this->resolveApiUser($request);
        if ($user && ! $user->hasPermission('measurements.view')) {
            return response()->json(['message' => 'Accès refusé : permission measurements.view requise.'], 403);
        }

        if ($user && $user->isCitizen() && ! $user->hasRole([Role::MANAGER, Role::ADMINISTRATEUR, Role::TECHNICIEN])) {
            if (! $mesure->is_verified) {
                return response()->json(['message' => 'Cette mesure n\'est pas encore validée et n\'est pas accessible au public.'], 403);
            }
        }

        return response()->json(['data' => $mesure->load('pointMesure.infrastructure')]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', 'unique:mesures_qualites,reference'],
            'point_mesure_id' => ['required', 'integer', 'exists:point_mesures,id'],
            'date_mesure' => ['required', 'date'],
            'ph' => ['required', 'numeric', 'between:0,14'],
            'turbidite' => ['required', 'numeric', 'min:0'],
            'chlore_residuel' => ['required', 'numeric', 'min:0'],
            'plomb' => ['required', 'numeric', 'min:0'],
            'nitrates' => ['required', 'numeric', 'min:0'],
            'is_verified' => ['sometimes', 'boolean'],
            'verifier' => ['nullable', 'string', 'max:255'],
        ]);

        $assessment = $this->waterQualityService->assess($validated);

        $mesure = DB::transaction(function () use ($validated, $assessment, $user) {
            $mesure = MesureQualite::create($validated + [
                'is_verified'        => $validated['is_verified'] ?? false,
                'status'             => $assessment['status'],
                'overall_compliance' => $assessment['overall_compliance'],
            ]);

            AuditLog::record('measurement_created', 'MesureQualite', $mesure->id, [
                'reference'  => $mesure->reference,
                'technicien' => $user->email,
            ], $user);

            return $mesure;
        });

        return response()->json([
            'message'    => 'La mesure a été enregistrée avec succès.',
            'data'       => $mesure->load('pointMesure.infrastructure'),
            'assessment' => $assessment,
        ], 201);
    }

    public function update(Request $request, MesureQualite $mesure): JsonResponse
    {
        $user = $this->resolveApiUser($request);

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (! $user->hasPermission('measurements.update')) {
            return response()->json(['message' => 'Accès refusé : permission measurements.update requise.'], 403);
        }

        // Technicians can only edit non-validated measurements
        if ($user->hasRole(Role::TECHNICIEN) && ! $user->hasRole([Role::MANAGER, Role::ADMINISTRATEUR])) {
            if ($mesure->is_verified) {
                return response()->json(['message' => 'Impossible de modifier une mesure déjà validée.'], 403);
            }
        }

        $validated = $request->validate([
            'date_mesure'     => ['sometimes', 'date'],
            'ph'              => ['sometimes', 'numeric', 'between:0,14'],
            'turbidite'       => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'chlore_residuel' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'plomb'           => ['sometimes', 'numeric', 'min:0', 'max:10000'],
            'nitrates'        => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'source_donnee'   => ['sometimes', 'string', Rule::in(MesureQualite::SOURCES)],
            'point_mesure_id' => ['sometimes', 'integer', 'exists:point_mesures,id'],
        ]);

        return response()->json([
            'message'    => 'La mesure a été mise à jour.',
            'data'       => $mesure->load('pointMesure.infrastructure'),
            'assessment' => $assessment,
        ]);
    }

    /**
     * Validate a measurement (Manager & Administrateur only).
     */
    public function validateMeasurement(Request $request, MesureQualite $mesure): JsonResponse
    {
        $user = $this->resolveApiUser($request);

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (! $user->hasPermission('measurements.validate')) {
            return response()->json([
                'message'             => 'Validation refusée : seuls les gestionnaires et administrateurs peuvent valider les mesures.',
                'required_permission' => 'measurements.validate',
            ], 403);
        }

        DB::transaction(function () use ($mesure, $user) {
            $mesure->update([
                'is_verified' => true,
                'verifier'    => $user->name . ' (' . $user->primary_role . ')',
            ]);

            AuditLog::record('measurement_validated', 'MesureQualite', $mesure->id, [
                'reference'    => $mesure->reference,
                'validated_by' => $user->email,
            ], $user);
        });

        return response()->json([
            'message' => "La mesure {$mesure->reference} a été validée avec succès.",
            'data'    => $mesure->load('pointMesure.infrastructure'),
        ]);
    }

    public function destroy(Request $request, MesureQualite $mesure): JsonResponse
    {
        $user = $this->resolveApiUser($request);

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (! $user->hasPermission('measurements.delete')) {
            return response()->json(['message' => 'Accès refusé : permission measurements.delete requise.'], 403);
        }

        DB::transaction(function () use ($mesure, $user) {
            $ref = $mesure->reference;
            $mesure->delete();

            AuditLog::record('measurement_deleted', 'MesureQualite', null, [
                'reference'  => $ref,
                'deleted_by' => $user->email,
            ], $user);
        });

        return response()->json(['message' => "La mesure {$mesure->reference} a été supprimée."]);
    }

    /**
     * Resolve the authenticated API user.
     *
     * Primary path: JWT guard (set by JwtAuthenticate middleware).
     * Fallback: web session guard (backward compatibility for Blade-originated XHR).
     */
    protected function resolveApiUser(Request $request): ?User
    {
        // Primary: JWT-authenticated user (JwtAuthenticate middleware already validated token + is_active)
        if ($jwtUser = auth('api')->user()) {
            /** @var User $jwtUser */
            return $jwtUser;
        }

        // Fallback: standard web session (backward compat for non-JWT callers)
        if ($webUser = auth()->user()) {
            /** @var User $webUser */
            return $webUser;
        }

        return null;
    }
}
