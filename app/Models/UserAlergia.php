<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAlergia extends Model
{
    protected $table = 'user_alergias';

    protected $fillable = [
        'user_id', 'alergia_id', 'gravedad', 'observaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alergia()
    {
        return $this->belongsTo(Alergia::class);
    }
}
