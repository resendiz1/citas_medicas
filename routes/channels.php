<?php

use App\Models\CitaMedica;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.cita.{citaId}', function ($user, $citaId) {
    $cita = CitaMedica::find($citaId);
    if (!$cita) return false;
    return (int) $user->id === (int) $cita->paciente_id || (int) $user->id === (int) $cita->medico_id;
});
