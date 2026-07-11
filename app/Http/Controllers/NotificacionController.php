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

        $notifications->each(function ($n) {
            $tipo = $n->data['tipo'] ?? null;
            if ($tipo !== 'mensaje') {
                $n->markAsRead();
            }
        });

        return response()->json($data);
    }

    public function marcarChatLeidas(Request $request)
    {
        $request->validate(['cita_id' => 'required|integer']);
        $user = $request->user();

        $user->unreadNotifications()
            ->where('data->tipo', 'mensaje')
            ->where('data->cita_id', $request->cita_id)
            ->get()
            ->each->markAsRead();

        return response()->json(['ok' => true]);
    }
}
