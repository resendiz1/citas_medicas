<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaChatMensaje extends Model
{
    protected $table = 'ia_chat_mensajes';

    protected $fillable = [
        'user_id', 'medico_id', 'role', 'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function scopeForAdminMedico($query, $userId, $medicoId)
    {
        return $query->where('user_id', $userId)->where('medico_id', $medicoId);
    }
}
