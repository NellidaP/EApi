<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    //
    protected $fillable = [
        'costo_monoambiente',
        'costo_dos_ambientes',
        'costo_tres_ambientes',
        'costo_cuatro_ambientes',
        'email_administrador',
    ];
}
