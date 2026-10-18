<?php

namespace App\Http\Resources\Technician;

use App\Services\FailureRiskService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfrastructureResource extends JsonResource
{
    /**
     * Transform the resource into an array for Technician role.
     * EXCLUT STRICTEMENT TOUTE DONNÉE DE COÛT OU DE BUDGET.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributes = [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => [
                'valeur' => $this->type?->value ?? $this->type,
                'libelle' => $this->type?->label() ?? (string) $this->type,
                'icone' => $this->type?->icon() ?? 'wrench',
            ],
            'etat' => [
                'valeur' => $this->etat?->value ?? $this->etat,
                'libelle' => $this->etat?->label() ?? (string) $this->etat,
                'badge' => $this->etat?->badgeClass() ?? 'bg-slate-500/10 text-slate-300',
            ],
            'niveau_criticite' => [
                'valeur' => $this->niveau_criticite?->value ?? $this->niveau_criticite,
                'libelle' => $this->niveau_criticite?->label() ?? (string) $this->niveau_criticite,
                'couleur' => $this->niveau_criticite?->color() ?? 'blue',
            ],
            'zone' => $this->whenLoaded('zone', function () {
                return [
                    'id' => $this->zone?->id,
                    'nom' => $this->zone?->nom,
                    'localisation' => $this->zone?->localisation,
                ];
            }),
            'capacite' => $this->capacite ? (float) $this->capacite : null,
            'unite_capacite' => $this->unite_capacite,
            'fabricant' => $this->fabricant,
            'reference' => $this->reference,
            'localisation' => $this->localisation,
            'coordonnees' => [
                'latitude' => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            'date_installation' => $this->date_installation?->toDateString(),
            'date_derniere_maintenance' => $this->date_derniere_maintenance?->toDateString(),
            'date_prochaine_maintenance' => $this->date_prochaine_maintenance?->toDateString(),
            'duree_vie_annees' => $this->duree_vie_annees,
            'sante' => $this->sante,
        ];

        $risk = $this->riskFields($request);
        if ($risk !== null) {
            $attributes['risk'] = $risk;
        }

        return $attributes;
    }

    /**
     * Champs de l'estimation de risque, visibles pour le rôle technicien uniquement.
     * Les clients citoyens ne reçoivent jamais ces données.
     *
     * @return array{
     *     estimation_indicative: bool,
     *     horizon_days: int,
     *     probability: int,
     *     level: string,
     *     factors: list<array<string, mixed>>,
     *     recommendation: string
     * }|null
     */
    private function riskFields(Request $request): ?array
    {
        $user = $request->hasSession() ? $request->session()->get('user') : null;
        if (! is_array($user) || ! in_array($user['role'] ?? '', ['manager', 'technician', 'admin'], true)) {
            return null;
        }

        $infrastructure = $this->resource;
        $prediction = $infrastructure->latestRiskPrediction;
        $calculated = app(FailureRiskService::class)->predict($infrastructure);
        $probability = $prediction?->probability ?? $calculated['probability'];
        $level = $prediction?->level ?? $calculated['level'];
        $factors = $prediction?->factors ?? $calculated['factors'];

        return [
            'estimation_indicative' => true,
            'horizon_days' => 30,
            'probability' => $probability,
            'level' => $level,
            'factors' => $factors,
            'recommendation' => app(FailureRiskService::class)->recommendationFor($factors, $level, null, $this->resource->id),
        ];
    }
}
