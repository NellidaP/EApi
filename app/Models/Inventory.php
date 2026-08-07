<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Traits\ModelTrait1;

class Inventory extends Api
{
    use ModelTrait1;

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
    //  "item_index": { "item_index": "item_index", 
    //      "cant": "item_cant" , 
    //      "code": "item_code", 
    //      "name":"item_name"
    //  }
    // }
    //

    // orders: array of orders performed on the inventory
    // {
    // "orders_index": {
    //   "orders_index":"orders_index",
    //   "items": {
    //     "item_index": {
//           "item_index": "item_index",
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
    //   "type": "inlet",
    //   "state": "created/cheked/ordered/received/canceled",
    //   "operation_index": "operation_index/null" // Optional: link to the operation that was created from this order
    //   "according": "true/false/null" // Optional: if the order was according to the operation or not, null if not checked yet
    // }
    // 

    // operations: array of operations performed on the inventory
    // {
    // "operation_index": { 
    //   "operation_index":"operation_index",
    //   "items": {
    //     "item_index": {
    //       "item_index": "item_index",
    //       "cant": "item_cant",
    //       "code": "item_code",
    //       "name": "item_name"
    //     }
    //   },
    //   "type": "inlet/outlet",
    //   "state": "pending/processed/canceled",
    //   "datetime_created": { "datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"},
    //   "datetime_updated": { "datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"},
    //   "datetime_processed": { "datetime":"YYYY-MM-DD hh:mm", "user_id":"user_id", "user_name":"user_name"},
    //   "order_index": "orders_index/null" // Optional: link to the order that generated this operation if was generated from an order
    //   "inventory_index": "inventory_index/null" // Optional: link to the inventory that received the items if was generated from an order
    //   "inventory_operation_index": "operation_index/null" // Optional: link to the operation that was created from this order in the inventory that receive if was generated from an order
    //  }
    // }

    //relacion de uno a muchos inversa con unity
    public function unity()
    {
        return $this->belongsTo(Unity::class);      
    }

    public function setOrdertoOperations($orderIndex)
    {
        $orders = $this->orders ?? [];
        if (isset($orders[$orderIndex])) {
            $order = $orders[$orderIndex];
            $operations = $this->operations ?? [];
            $operation_index = now()->toDateTimeString();
            $operations[$operation_index] = [
                'items' => $order['items'],
                'type' => $order['type']?? 'inlet', // Default to 'inlet' if not specified
                'order_index' => $orderIndex, // link to the order that generated this operation
            ];
            $operations[$operation_index]['datetime'] = [
                'datetime' => now()->toDateTimeString(),
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ];
            $orders[$orderIndex]['operation_index'] = $operation_index; // link to the operation that was created from this order
            $this->orders = $orders;
            $this->operations = $operations;
            $this->save();

            return $operation_index; // Return the operation index that was created
        }

        return null; // Return null if the order does not exist
    }

    public function applyOperation($operationIndex)
    {
        $operations = $this->operations ?? [];
        $processedItems = [];
        
        if (isset($operations[$operationIndex]) && (($operations[$operationIndex]['state'] ?? 'pending') === 'pending')) {
            $operation = $operations[$operationIndex];
            $items = $this->items ?? [];

            foreach ($operation['items'] as $itemKey => $itemData) {
                $itemCode = $itemData['code'] ?? null;
                
                if ($operation['type'] === 'inlet') {
                    // Add items to inventory
                    $found = false;
                    foreach ($items as $key => &$item) {
                        if (($item['code'] ?? null) === $itemCode) {
                            $item['cant'] += $itemData['cant'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $items[$itemKey] = $itemData;
                    }
                } elseif ($operation['type'] === 'outlet') {
                    // Remove items from inventory
                    foreach ($items as $key => &$item) {
                        if (($item['code'] ?? null) === $itemCode) {
                            $item['cant'] -= $itemData['cant'];
                            /* if ($item['cant'] <= 0) {
                                unset($items[$key]);
                            } */
                            break;
                        }
                    }
                    // Track processed items for outlet operations
                    $processedItems[$itemCode] = $itemData['cant'];
                }
            }
            $operations[$operationIndex]['state'] = 'processed';
            $operations[$operationIndex]['datetime_processed'] = [
                'datetime' => now()->toDateTimeString(),
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ];

            // Update the inventory items and save
            $this->operations = $operations;
            $this->items = $items;
            $this->save();
        }

        // Return processed items if outlet operation
        if (isset($operations[$operationIndex]) && $operations[$operationIndex]['type'] === 'outlet') {
            return $processedItems;
        }
        
        return [];
    }

    public function updateItemsWithOperations()
    {
        $this->items = []; // Reset items
        $this->save(); // Save the reset state before applying operations
        $operations = $this->operations ?? [];

        foreach ($operations as $operationIndex => $operation) {
            if (isset($operation['state']) && $operation['state'] === 'processed') {
                $this->applyOperation($operationIndex);
            }
        }
    }

    public function updateOperationWithOrder( )
    {
        
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

    public function addOrder($items)
    {
        $orders = $this->orders ?? [];
        $orderIndex = now()->toDateTimeString();
        $orders[$orderIndex] = [
            'items' => $items,
            'datetimes' => [
                'created' => [
                    'datetime' => now()->toDateTimeString(),
                    'user_id' => auth()->id() ?? null,
                    'user_name' => auth()->user()->name ?? null,
                ],
            ],
            'state' => 'created',
        ];
        $this->orders = $orders;
        $this->setHasOrderPending();
        $this->save();
    }

    public function updateOrder($orderIndex, $items)
    {
        $orders = $this->orders ?? [];
        if (isset($orders[$orderIndex])) {
            $orders[$orderIndex]['items'] = $items;
            $this->orders = $orders;
            $this->save();
        }
    }

    public function changeOrderState($orderIndex, $newState)
    {
        $orders = $this->orders ?? [];
        if (isset($orders[$orderIndex])) {
            $orders[$orderIndex]['state'] = $newState;
            $orders[$orderIndex]['datetimes'][$newState] = [
                'datetime' => now()->toDateTimeString(),
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ];
            $this->orders = $orders;
            if ($newState === 'received') {
                $this->clearHasOrderPending();
            }
            $this->save();
        }
    }

    public function getOrderState($orderIndex)
    {
        $orders = $this->orders ?? [];
        if (isset($orders[$orderIndex])) {
            return $orders[$orderIndex]['state'];
        }
        return null;
    }

    public function deleteOrder($orderIndex)
    {
        $orders = $this->orders ?? [];
        if (isset($orders[$orderIndex])) {
            unset($orders[$orderIndex]);
            $this->orders = $orders;
            $this->save();
        }
    }


    public function addOperation($items, $type = 'inlet', $orderIndex = null, 
                                $inventoryIndex = null, $inventoryOperationIndex = null)
    {
        $operations = $this->operations ?? [];
        $operationIndex = now()->toDateTimeString();
        $operations[$operationIndex] = [
            'operation_index' => $operationIndex,
            'items' => $items,
            'type' => $type,
            'state' => 'pending',
            'order_index' => $orderIndex,
            'inventory_index' => $inventoryIndex,
            'inventory_operation_index' => $inventoryOperationIndex,
            'datetime_created' => [
                'datetime' => $operationIndex,
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ],
            'datetime_updated' => [
                'datetime' => $operationIndex,
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ],
        ];
        $this->operations = $operations;
        $this->save();

        return $operationIndex; // Return the operation index and processed items if outlet
    }

    public function updateOperation($operationIndex, $items, $type = null)
    {
        
        $operations = $this->operations ?? [];
        $currentType = $operations[$operationIndex]['type'] ?? null;
        if (isset($operations[$operationIndex])) {
            $operations[$operationIndex]['items'] = $items;
            $operations[$operationIndex]['type'] = $type ?? $currentType; // Keep the current type if not provided
            $operations[$operationIndex]['datetime_updated'] = [
                'datetime' => now()->toDateTimeString(),
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
            ];
            $this->operations = $operations; 
            $this->save();
            $this->updateItemsWithOperations(); // Update items based on the updated operation  
        }
    }

    public function deleteOperation($operationIndex)
    {
        $operations = $this->operations ?? [];
        if (isset($operations[$operationIndex])) {
            unset($operations[$operationIndex]);
            $this->operations = $operations;
            $this->save();
             $this->updateItemsWithOperations(); // Update items based on remaining operations
        }

    }

    public function passItemsBetweenInventoriesUsingOperation( Inventory $targetInventory, $items)
    {
        // Create an outlet operation in the current inventory
        $processedItems = $this->addOperation($items, 'outlet');

        // Create an inlet operation in the target inventory
        $targetInventory->addOperation($processedItems[1], 'inlet', null, $this->id, $processedItems[0]);

        return [
            'source_inventory' => $this,
            'target_inventory' => $targetInventory,
            'processed_items' => $processedItems,
        ];
    }

    public function updateItemsBetweenInventoriesUsingOperation(  $items, $sourceOperationIndex)
    {
        // Get the target operation index from the source operation's inventory_operation_index
        $targetInventory = Inventory::find($this->operations[$sourceOperationIndex]['inventory_index'] ?? null);
        $operations = $this->operations ?? [];
        $targetOperationIndex = $operations[$sourceOperationIndex]['inventory_operation_index'] ?? null;

        if ($targetOperationIndex) {
            // Update the outlet operation in the current inventory
            $this->updateOperation($sourceOperationIndex, $items, 'outlet');

            // Update the inlet operation in the target inventory
            $targetInventory->updateOperation($targetOperationIndex, $items, 'inlet');

            return [
                'source_inventory' => $this,
                'target_inventory' => $targetInventory,
                'source_operation_index' => $sourceOperationIndex,
                'target_operation_index' => $targetOperationIndex,
            ];
        } else {
            throw new \Exception("Target operation index not found for source operation index: " . $sourceOperationIndex);
        }
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

    /**
     * Get all inventories with pending orders.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingOrders()
    {
        return self::where('has_order_pending', true)->get();
    }

    /**
     * Get all orders with a given state.
     *
     * @param string $state The state to filter by: created, cheked, ordered, received
     * @return array Array of orders matching the given state
     */
    public function getOrdersByState($state)
    {
        $orders = $this->orders ?? [];
        $filteredOrders = [];
        
        foreach ($orders as $orderIndex => $order) {
            if (isset($order['state']) && $order['state'] === $state) {
                $filteredOrders[$orderIndex] = $order;
            }
        }
        
        return $filteredOrders;
    }

    public function getOrdersNoReceivedNoCanceled()
    {
        $orders = $this->orders ?? [];
        $filteredOrders = [];

        foreach ($orders as $orderIndex => $order) {
            if (isset($order['state']) && $order['state'] !== 'received' && $order['state'] !== 'canceled') {
                $filteredOrders[$orderIndex] = $order;
            }
        }

        return $filteredOrders;
    }

    /**
     * Search for all orders with pending states and update has_order_pending column.
     * Pending states are: created, checked, ordered
     */
    public function updateHasOrderPendingByState()
    {
        $orders = $this->orders ?? [];
        $pendingStates = ['created', 'checked', 'ordered'];
        $hasPending = false;

        foreach ($orders as $order) {
            if (isset($order['state']) && in_array($order['state'], $pendingStates)) {
                $hasPending = true;
                break;
            }
        }

        $this->has_order_pending = $hasPending;
        $this->save();

        return $hasPending;
    }

    

}

