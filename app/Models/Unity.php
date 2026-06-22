<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unity extends Api
{
    /** @use HasFactory<\Database\Factories\UnityFactory> */
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'unity_users', 'unity_id', 'user_id');
    }

    // Self-referential one-to-many: a Unity may have a parent Unity
    public function parent()
    {
        return $this->belongsTo(Unity::class, 'unity_id');
    }

    // Self-referential one-to-many: a Unity may have many child Unities
    public function children()
    {
        return $this->hasMany(Unity::class, 'unity_id');
    }

    /**
     * Obtener todos los descendientes de esta unidad de forma recursiva.
     * Devuelve una colección con todos los hijos, nietos, bisnietos, etc.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allDescendants()
    {
        $descendants = collect();

        $children = $this->children()->get();

        foreach ($children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->allDescendants());
        }

        return $descendants;
    }

    
}

