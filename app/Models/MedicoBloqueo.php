<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoBloqueo extends Model
{
    protected $table = 'medico_bloqueos';

    protected $fillable = [
        'medico_id', 'fecha_inicio', 'fecha_fin', 'motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}
