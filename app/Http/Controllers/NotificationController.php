<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Toutes les notifications de l'utilisateur connecté (filtre lues / non lues).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $filtre = $request->query('filtre');

        $notifications = $user->notifications()
            ->when($filtre === 'non-lues', fn ($query) => $query->whereNull('read_at'))
            ->when($filtre === 'lues', fn ($query) => $query->whereNotNull('read_at'))
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filtre' => $filtre,
            'total' => $user->notifications()->count(),
            'nonLues' => $user->unreadNotifications()->count(),
            'aujourdhui' => $user->notifications()->whereDate('created_at', today())->count(),
        ]);
    }

    /**
     * Marque la notification comme lue puis ouvre l'élément concerné.
     */
    public function ouvrir(string $id)
    {
        $notification = Auth::user()?->notifications()->findOrFail($id);
        abort_unless($notification, 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function toutLire(Request $request)
    {
        Auth::user()?->unreadNotifications->markAsRead();

        return $request->expectsJson()
            ? response()->json(['message' => 'Toutes les notifications sont marquées comme lues.'])
            : back()->with('success', 'Toutes les notifications sont marquées comme lues.');
    }
}
