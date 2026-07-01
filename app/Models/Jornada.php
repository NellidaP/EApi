<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jornada extends Api
{
    /** @use HasFactory<\Database\Factories\JornadaFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'longitud',
        'latitud',
        'estado',
        'entrada',
        'aprobador_id',
        'comentario',
        'url_in',
        'url_out',
        'unity_in_id',
        'unity_out_id',
        'fechahora_ini',
        'fechahora_fin',
    ];
}
