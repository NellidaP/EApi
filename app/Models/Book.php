<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait1;

class Book extends Api
{
    //
    use ModelTrait1;

    protected $fillable = [
        'name',
        'data',
        'description',
        'user_id', // Foreign key to associate the book with a user
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
