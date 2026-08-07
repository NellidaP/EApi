<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait1;

class Store extends Model
{
    use ModelTrait1;
    //
    protected $fillable = [
        'name',
        'description',
        'supplier',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    // items: array of items in the store
    // {
    // "item_id": {
    //   "item_id": "item_id",
    //   "name": "item_name",
    //   "code": "item_code",
    //   "cost": "item_cost"
    // }

    /* public function getItemsAttribute($value)
    {
        return $this->getDataJson($value, 'items');
    } */

    public function addItem( $name, $code, $cost)
    {
        $itemId = uniqid(); // Generate a unique ID for the item
        $items = $this->items ?? [];
        $items[$itemId] = [
            'item_id' => $itemId,
            'name' => $name,
            'code' => $code,
            'cost' => $cost,
        ];
        $this->setDataJson($items, null, 'items');
        $this->save();
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

    public function removeItem($itemId)
    {
        $items = $this->getDataJson(null, 'items');
        if (isset($items[$itemId])) {
            unset($items[$itemId]);
            $this->setDataJson($items, null, 'items');
            $this->save();
        }
    }

    public function getAllItems()
    {
        return json_decode($this->items, true) ?? [];
    }



}
