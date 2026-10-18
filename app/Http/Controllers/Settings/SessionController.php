<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id() ?? $request->session()->get('user.id');
        $currentSessionId = $request->session()->getId();
        $sessions = collect();

        if ($userId && config('session.driver') === 'database') {
            $sessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $userId)->orderByDesc('last_activity')->get()
                ->map(fn (object $session): array => [
                    'id' => $session->id,
                    'current' => hash_equals((string) $session->id, $currentSessionId),
                    'device' => $this->deviceLabel($session->user_agent),
                    'ip' => $session->ip_address ?: 'Adresse inconnue',
                    'location' => $session->ip_address ?: 'Adresse inconnue',
                    'time' => \Illuminate\Support\Carbon::createFromTimestamp((int) $session->last_activity)->diffForHumans(),
                    'last_activity' => (int) $session->last_activity,
                ]);
        } elseif ($userId && config('session.driver') === 'file') {
            $sessions = collect(File::files(config('session.files')))
                ->map(function ($file) use ($userId, $currentSessionId): ?array {
                    $data = $this->readFileSession($file->getPathname());
                    if (! $this->belongsToUser($data, $userId)) {
                        return null;
                    }

                    $lastActivity = $file->getMTime();
                    if ($lastActivity < now()->subMinutes(config('session.lifetime'))->timestamp) {
                        return null;
                    }

                    return [
                        'id' => $file->getFilename(),
                        'current' => hash_equals($file->getFilename(), $currentSessionId),
                        'device' => 'Appareil inconnu · Navigateur inconnu',
                        'ip' => 'Information non enregistrée',
                        'location' => 'Information non enregistrée',
                        'time' => \Illuminate\Support\Carbon::createFromTimestamp($lastActivity)->diffForHumans(),
                        'last_activity' => $lastActivity,
                    ];
                })
                ->filter()
                ->sortByDesc('last_activity')
                ->values();
        }

        return view('settings.index', compact('sessions', 'currentSessionId'));
    }

    public function revoke(Request $request, string $sessionId): RedirectResponse
    {
        abort_if(hash_equals($request->session()->getId(), $sessionId), 422, 'La session actuelle ne peut pas être révoquée depuis cet écran.');
        $userId = Auth::id() ?? $request->session()->get('user.id');
        abort_unless($userId && $this->validSessionId($sessionId), 404);
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->where('user_id', $userId)->delete();
        } elseif (config('session.driver') === 'file') {
            $path = rtrim(config('session.files'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $sessionId;
            abort_unless(File::exists($path) && $this->belongsToUser($this->readFileSession($path), $userId), 404);
            File::delete($path);
        } else {
            abort(404);
        }

        return back()->with('success', 'La session sélectionnée a été révoquée.');
    }

    public function revokeOthers(Request $request): RedirectResponse
    {
        $userId = Auth::id() ?? $request->session()->get('user.id');
        abort_unless($userId, 404);
        $currentSessionId = $request->session()->getId();
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $userId)->where('id', '!=', $currentSessionId)->delete();
        } elseif (config('session.driver') === 'file') {
            $this->deleteFileSessions(fn (array $data, string $id): bool => $id !== $currentSessionId && $this->belongsToUser($data, $userId));
        } else {
            abort(404);
        }

        return back()->with('success', 'Toutes les autres sessions de votre compte ont été révoquées.');
    }

    public function forceRenewAll(Request $request): RedirectResponse
    {
        $currentSessionId = $request->session()->getId();
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('id', '!=', $currentSessionId)->delete();
        } elseif (config('session.driver') === 'file') {
            $this->deleteFileSessions(fn (array $data, string $id): bool => $id !== $currentSessionId);
        } else {
            abort(404);
        }

        return back()->with('success', 'Les autres sessions de l’application ont été révoquées.');
    }

    private function deviceLabel(?string $userAgent): string
    {
        $agent = (string) $userAgent;
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Navigateur inconnu',
        };
        $platform = match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Appareil inconnu',
        };

        return $platform . ' · ' . $browser;
    }

    private function readFileSession(string $path): array
    {
        try {
            $contents = File::get($path);
            if (config('session.encrypt')) {
                $contents = Crypt::decryptString($contents);
            }

            $data = config('session.serialization') === 'json'
                ? json_decode($contents, true)
                : @unserialize($contents, ['allowed_classes' => false]);

            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function belongsToUser(array $data, int|string $userId): bool
    {
        if ((string) data_get($data, 'user.id') === (string) $userId) {
            return true;
        }

        $authKey = Auth::guard()->getName();
        return isset($data[$authKey]) && (string) $data[$authKey] === (string) $userId;
    }

    private function validSessionId(string $sessionId): bool
    {
        return preg_match('/^[A-Za-z0-9,-]{1,128}$/', $sessionId) === 1;
    }

    private function deleteFileSessions(callable $shouldDelete): void
    {
        foreach (File::files(config('session.files')) as $file) {
            $id = $file->getFilename();
            if ($this->validSessionId($id) && $shouldDelete($this->readFileSession($file->getPathname()), $id)) {
                File::delete($file->getPathname());
            }
        }
    }
}
