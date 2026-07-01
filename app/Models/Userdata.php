<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait1;

class UserData extends Api
{
    /** @use HasFactory<\Database\Factories\UserDataFactory> */
    use HasFactory, ModelTrait1;

    protected $fillable = [
        'user_id',
        'data',
        'data_ticket',
        'documents',
        'type',
        
        // Agrega aquí otros campos que quieras permitir asignar masivamente
    ];

    protected $table = 'userdatas';


    public function user()
    {
        return $this->belongsTo(User::class);  
    }
}
