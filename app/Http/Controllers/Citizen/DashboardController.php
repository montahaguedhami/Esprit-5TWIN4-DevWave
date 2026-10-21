<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\InfrastructureAlerte;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $citizenReports = Incident::query()->where('user_id', $user->id);
$resolvedStatuses = ['resolu', 'cloture'];
        $reports = (clone $citizenReports)
            ->with(['zone:id,nom', 'infrastructure:id,nom'])
            ->orderByDesc('date_signalement')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
        $reportCount = (clone $citizenReports)->count();
        $resolvedCount = (clone $citizenReports)->whereIn('statut', $resolvedStatuses)->count();

        $notificationsAvailable = Schema::hasTable('notifications');
        $notifications = $notificationsAvailable
            ? $user->notifications()->latest()->limit(5)->get()
            : collect();
        $unreadNotificationCount = $notificationsAvailable
            ? $user->notifications()->whereNull('read_at')->count()
            : 0;

        $alerts = Schema::hasTable('infrastructure_alertes')
            ? InfrastructureAlerte::query()
                ->with('infrastructure.zone')
                ->where('resolue', false)
                ->latest()
                ->limit(3)
                ->get()
            : collect();

        $announcements = Schema::hasTable('interventions')
            ? Intervention::query()
                ->with('infrastructure.zone')
                ->where(function ($query): void {
                    $query->where('statut', 'En cours')
                        ->orWhere('statut', 'like', 'Planifi%');
                })
                ->where(function ($query): void {
                    $query->where('statut', 'En cours')
                        ->orWhereDate('date', '>=', today());
                })
                ->orderBy('date')
                ->limit(3)
                ->get()
            : collect();

        return view('citizen.dashboard', [
            'userName' => $user->name,
            'reports' => $reports,
            'reportCount' => $reportCount,
            'activeReportCount' => max(0, $reportCount - $resolvedCount),
            'notifications' => $notifications,
            'unreadNotificationCount' => $unreadNotificationCount,
            'alerts' => $alerts,
            'announcements' => $announcements,
        ]);
    }
}