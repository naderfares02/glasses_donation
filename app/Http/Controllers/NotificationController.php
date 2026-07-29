<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'unread'); // unread | all

        $user = $request->user();

        $notifications = $tab === 'all'
            ? $user->notifications()->latest()->paginate(15)->withQueryString()
            : $user->unreadNotifications()->latest()->paginate(15)->withQueryString();

        $counts = [
            'unread' => $user->unreadNotifications()->count(),
            'all'    => $user->notifications()->count(),
        ];

        return view('notifications.index', compact('notifications', 'tab', 'counts'));
    }

    public function markRead(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        $url = $n->data['url'] ?? null;

        return $url ? redirect($url) : back();
    }

    public function open(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        $url = $n->data['url'] ?? null;

        return $url ? redirect($url) : redirect()->route('notifications.index');
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }
}