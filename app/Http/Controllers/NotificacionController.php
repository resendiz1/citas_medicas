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

        if ($data->isNotEmpty()) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json($data);
    }
}
