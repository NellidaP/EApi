<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Api
{
    /** @use HasFactory<\Database\Factories\UserDataFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data',
        'data_ticket',
        // Agrega aquí otros campos que quieras permitir asignar masivamente
    ];

    protected $table = 'userdatas';


    public function User()
    {
        return $this->belongsTo(User::class);  
    }
}
