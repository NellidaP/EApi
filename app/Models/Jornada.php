<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait1;

class Jornada extends Api
{
    /** @use HasFactory<\Database\Factories\JornadaFactory> */
    use HasFactory, ModelTrait1;

    protected $fillable = [
        'user_id',
        'longitud',
        'latitud',
        'estado',
        'ent',
        'aprobador_id',
        'comentario',
        'url_in',
        'url_out',
        'unity_in_id',
        'unity_out_id',
        'fechahora_ini',
        'fechahora_fin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function unityIn()
    {
        return $this->belongsTo(Unity::class, 'unity_in_id');
    }
    public function unityOut()
    {
        return $this->belongsTo(Unity::class, 'unity_out_id');
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

        
        //dd($query->toSql(), $query->getBindings());

        if (request()->has('perPage')) {

            return $query->paginate(request()->query('perPage'));
        }

        return $query->get();
 
    }

}
