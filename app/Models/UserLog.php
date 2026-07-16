<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $table = 'user_logs';

    protected $fillable = [
        'user_id', 'ip', 'user_agent', 'so', 'navegador',
        'url', 'route_name', 'method', 'accion',
        'model_type', 'model_id', 'lat', 'lng',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
