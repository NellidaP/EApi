<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Inventory extends Model
{
    //
    protected $fillable = [
        'name',
        'description',
        'unity_id',
        'type',
        'items',
        'operations',
        'orders',
        'has_order_pending',
    ];

    protected $casts = [
        'items' => AsArrayObject::class,
        'operations' => AsArrayObject::class,
        'orders' => AsArrayObject::class,
        'has_order_pending' => 'boolean',
    ];

    // items: array of items in the inventory
    // {
    // "item_index": { "cant": "item_cant" , "code": "item_code", "name":"item_name"}
    // }
    //

    // orders: array of orders performed on the inventory
    // {
    // "orders_index": {
    //   "orders_index":"orders_index",
    //   "items": {
    //     "item_code": {
    //       "cant": "item_cant",
    //       "code": "item_code",
    //       "name": "item_name"
    //     }
    //   },
    //   "datetimes": { "created":{"datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"},
    //                   "cheked":{"datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"},
    //                   "ordered":{"datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"},
    //                   "received":{"datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"}
    //   },
    //   "state": "create/cheked/ordered/received"
    // }
    // }

    // operations: array of operations performed on the inventory
    // {
    // "operations": {
    //   "operations":"orders_index",
    //   "items": {
    //     "item_code": {
    //       "cant": "item_cant",
    //       "code": "item_code",
    //       "name": "item_name"
    //     }
    //   },
    //   "type": "inlet/outlet",
    //   "datetime": { "datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"}
    // }
    // }

    //relacion de uno a muchos inversa con unity
    public function unity()
    {
        return $this->belongsTo(Unity::class);      
    }

    public function setOrdertoOperations($orderIndex, $type = 'inlet')
    {
        $orders = $this->orders ?? [];
        if (isset($orders[$orderIndex])) {
            $order = $orders[$orderIndex];
            $operations = $this->operations ?? [];
            $operations[now()->toDateTimeString()] = [
                'items' => $order['items'],
                'type' => $type, // or 'outlet' based on your logic
            ];
            $operations[now()->toDateTimeString()]['datetime'] = [
                'datetime' => now()->toDateTimeString(),
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ];
            $this->operations = $operations;
            $this->save();
        }

        
    }

    public function applyOperation($operationIndex)
    {
        $operations = $this->operations ?? [];
        if (isset($operations[$operationIndex])) {
            $operation = $operations[$operationIndex];
            $items = $this->items ?? [];

            foreach ($operation['items'] as $itemCode => $itemData) {
                if ($operation['type'] === 'inlet') {
                    // Add items to inventory
                    if (isset($items[$itemCode])) {
                        $items[$itemCode]['cant'] += $itemData['cant'];
                    } else {
                        $items[$itemCode] = $itemData;
                    }
                } elseif ($operation['type'] === 'outlet') {
                    // Remove items from inventory
                    if (isset($items[$itemCode])) {
                        $items[$itemCode]['cant'] -= $itemData['cant'];
                        if ($items[$itemCode]['cant'] <= 0) {
                            unset($items[$itemCode]);
                        }
                    }
                }
            }

            // Update the inventory items and save
            $this->items = $items;
            $this->save();
        }


    }

    public function updateItemsWithOperations()
    {
        $operations = $this->operations ?? [];
        foreach ($operations as $operationIndex => $operation) {
            $this->applyOperation($operationIndex);
        }
    }

    public function updateOperationWithOrder($orderIndex, $type = 'inlet')
    {
        $this->setOrdertoOperations($orderIndex, $type);
    }

    public function setHasOrderPending()
    {
        $this->has_order_pending = true;
        $this->save();
    }

    public function clearHasOrderPending()
    {
        $this->has_order_pending = false;
        $this->save();
    }


}
