<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alergia extends Model
{
    protected $table = 'alergias';

    protected $fillable = ['nombre', 'descripcion'];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'user_alergias', 'alergia_id', 'user_id')
            ->withPivot('gravedad', 'observaciones')
            ->withTimestamps();
    }
}
