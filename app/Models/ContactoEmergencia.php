<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoEmergencia extends Model
{
    protected $table = 'contactos_emergencia';

    protected $fillable = [
        'user_id', 'nombre_completo', 'telefono',
        'email', 'parentesco', 'direccion', 'principal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
