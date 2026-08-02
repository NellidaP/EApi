<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait1;

class Unity extends Api
{
    /** @use HasFactory<\Database\Factories\UnityFactory> */
    use HasFactory, ModelTrait1;

    protected $fillable = [
        'name',
        'description',
        'unity_id', // This is the foreign key for the parent Unity
        'direction',
        'longitud',
        'latitud',
        'type',
        'tickets',
        'mult',
        'costo_ficha',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('type');
    }

    public function books()
    {
        return $this->morphMany(Book::class, 'bookable');
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

    /**
     * Obtener todos los ancestros de esta unidad en orden de ascendencia.
     * Devuelve una colección con el padre, el abuelo, bisabuelo, etc.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent()->first();

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent()->first();
        }

        // Invertir el orden: devolver desde el ancestro más lejano hasta el padre directo
        return $ancestors->reverse()->values();
    }

    public function scopeGetOrPaginate($query)
    {

        $user_type = auth()->user()->type ?? null;
        
        if(request('select')){
             $select = request('select');
             $selectArray = explode(',', $select);
             $query->select($selectArray);
        }
    
    
        if (request('include')) {
            $include = explode(',', request('include'));
            $query->with($include);
        }

        if ($user_type === 3) {
            $query->whereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        
        

        if(request('filters')){
            $filters = request('filters');
            foreach ($filters as $field => $conditions) {
            foreach ($conditions as $operator => $value) {
                if (in_array($operator, ['=', '>', '<', '>=', '<=', '!='])) {
                    $query->where($field, $operator, $value);
                } 

                if ($operator == 'like') {
                    $query->where($field, 'like', "%$value%");
                }
            }
        }
        }



        if(request('sort')){
            $sortFields = explode(',', request('sort'));

            foreach ($sortFields as $sortField) {
                
                $direction = 'asc';

                if (substr($sortField, 0, 1) == '-') {
                    $direction = 'desc';
                    $sortField = substr($sortField, 1);
                }

                $query->orderBy($sortField, $direction);
            }
        }

        
        //dd($query->toSql(), $query->getBindings());

        if (request()->has('perPage')) {

            return $query->paginate(request()->query('perPage'));
        }

        return $query->get();
 
    }

    public function jornadasIn()
    {
        return $this->hasMany(Jornada::class, 'unity_in_id');
    }

    public function jornadasOut()
    {
        return $this->hasMany(Jornada::class, 'unity_out_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function servicesBetweenDates($startDate, $endDate)
    {
        return $this->services()
                    ->whereBetween('fecha_inicio', [$startDate, $endDate])
                    ->get();
    }

    


}

