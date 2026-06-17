<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdata extends Model
{
    /** @use HasFactory<\Database\Factories\UserdataFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data',
        'data_ticket',
        // Agrega aquí otros campos que quieras permitir asignar masivamente
    ];
}
