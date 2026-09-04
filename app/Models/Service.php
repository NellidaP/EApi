<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait1;

class Service extends Api
{
    use ModelTrait1;

    protected $fillable = [
        'description',
        'tipo', // 0: Nomal, 1: Mucama, 2: Final de Obra
        'estado', // 0: Creado, 1: Asignado, 2: Ejecutado, 3: Supervisado, 4: Procesado, 5: Pagado , 6: Cancelado
        'tipo_pago', // 0: Efectivo, 1: Tarjeta, 2: Transferencia
        'n_fichas',
        'n_personas',
        'costo_ficha',
        'tipo_ambiente', // 0: Normal, 1: Monoambiente, 2: DosAmbientes , 3: TripleAmbiente, 4: CuatroAmbientes
        'costo_ambiente',
        'costo_asignado',
        'costo_hora',
        'fecha_inicio',
        'tiempo_horas',
        'costo_total',
        'unity_id',
        'user_id',
        'users', // Array of users associated ( that does the service) with the service
        'items', // Array of items associated with the service
    ];


    // items
    // {
    //  "item_id": {
    //          "name": "Name Of Item",
    //          "id": item_id,
    //          "costo": "cost_of_items",
    //          "cantidad": "cantidad_of_items"
    //      }
    // }
    // users
    // {
    //  "user_id": {
    //          "name": "Name Of User",
    //          "id": user_id
    //      }
    // }

    public function unity()
    {
        return $this->belongsTo(Unity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUsersData()
    {
        return $this->getDataJson(null, 'users');
    }

    public function setUsersData($value)
    {
        $this->setDataJson($value, null, 'users');
        $this->save();
    }

    public function chat(){
        return $this->morphOne(Chat::class, 'chatable');
    }

    public function addUser(User $user)
    {
        $users = $this->getUsersData();
        if (!in_array($user->id, $users)) {
            $users[$user->id] = [
                'name' => $user->name,
                'id' => $user->id,
            ];
            $this->setUsersData($users);
        }
    }

    public function removeUser($userId)
    {
        $users = $this->getUsersData();
        if (isset($users[$userId])) {
            unset($users[$userId]);
            $this->setUsersData($users);
        }
    }

    public function getItemsData()
    {
        return $this->getDataJson(null, 'items');
    }

    public function addOrUpdateItem($id,$name,$costo,$cantidad)
    {
        $items = $this->getItemsData();
        $found = false;
        foreach ($items as &$item) {
            if ($item['id'] == $id) {
                $item['name'] = $name;
                $item['costo'] = $costo;
                $item['cantidad'] = $cantidad;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->addItem($id,$name,$costo,$cantidad);
        } else {
            $this->setItemsData($items);
        }
    }
    
    public function addItem($id,$name,$costo,$cantidad)
    {
        $item = [
            'id' => $id,
            'name' => $name,
            'costo' => $costo,
            'cantidad' => $cantidad,
        ];
        $this->setItemsData(array_merge($this->getItemsData(), [$item]));
    }
    

    public function setItemsData($value)
    {   
        $total=0;
        foreach($value as $item){
            $total += $item['costo'] * $item['cantidad'];
        }
        $this->costo_total = $total+$this->costo_asignado+
                            $this->costo_ambiente+
                            $this->costo_hora*$this->tiempo_horas+
                            $this->costo_ficha*$this->n_fichas;
        $this->save();
        $this->setDataJson($value, null, 'items');

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

       if($user_type == 3){
            $query->where('user_id', auth()->id());
        }





        if(request('flags')){
            $flags = request('flags');
            if(isset($flags['users'])){
                $userId = $flags['users'];
                $query->whereJsonContains('users->' . $userId, [
                    'id' => (int)$userId,
                ]);
            }
        }

        if($user_type == 0){
            $query->whereJsonContains('users->' . auth()->id(), [
                'id' => (int)auth()->id(),
            ]);
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

}




