<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionController extends Controller
{
    public function poll(Request $request)
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications()->latest()->get();

        $data = $notifications->map(function ($n) {
            $payload = $n->data;
            $payload['id'] = $n->id;
            $payload['type'] = class_basename($n->type);
            $payload['time'] = $n->created_at->diffForHumans();
            return $payload;
        });

        return response()->json($data);
    }

    public function dropdown(Request $request)
    {
        $user = $request->user();
        $unreadCount = $user->unreadNotifications()->where('data->tipo', '!=', 'mensaje')->count();
        $notifications = $user->notifications()->where('data->tipo', '!=', 'mensaje')->latest()->take(10)->get()->map(function ($n) {
            return [
                'id'       => $n->id,
                'message'  => $n->data['message'] ?? 'Notificación',
                'read_at'  => $n->read_at,
                'time'     => $n->created_at->diffForHumans(),
                'cita_id'  => $n->data['cita_id'] ?? null,
                'estado'   => $n->data['estado'] ?? null,
            ];
        });

        return response()->json([
            'unread' => $unreadCount,
            'items'  => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notif = DatabaseNotification::findOrFail($id);
        if ($notif->notifiable_id === auth()->id()) {
            $notif->markAsRead();
        }
        return response()->json(['ok' => true]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->where('data->tipo', '!=', 'mensaje')
            ->latest()
            ->simplePaginate(20);

        return view('notificaciones.index', compact('notifications'));
    }
}
