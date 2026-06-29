<?php

namespace App\Models;

use App\Models\Scopes\FilterScope;
use App\Models\Scopes\IncludeScope;
use App\Models\Scopes\SelectScope;
use App\Models\Scopes\SortScope;
use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScopes([
            //FilterScope::class,
            SelectScope::class,
            SortScope::class,
            //IncludeScope::class
        ]);
    }

    public function scopeGetOrPaginate($query)
    {
        if(request('select')){
             $select = request('select');
             $selectArray = explode(',', $select);
             $query->select($selectArray);
        }

        if (request('include')) {
            $include = explode(',', request('include'));
            $query->with($include);
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
        if (request()->has('perPage')) {
            return $query->paginate(request()->query('perPage'));
        }
        

        return $query->get();
        
    }
}
