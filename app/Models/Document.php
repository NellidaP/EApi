<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Api
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'url',
        'documentable_type',
        'documentable_id',
        'type',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }
}
