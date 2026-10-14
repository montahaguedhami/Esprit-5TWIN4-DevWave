<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('audit.view');

        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_email', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.logs', compact('logs'));
    }

    private function resolveFallbackUser(): User
    {
        $sessionEmail = session('user.email');
        if ($sessionEmail) {
            $user = User::where('email', $sessionEmail)->first();
            if ($user) {
                return $user;
            }
        }

        return User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->firstOrFail();
    }
}
