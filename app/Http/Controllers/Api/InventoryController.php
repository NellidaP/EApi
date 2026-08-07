<?php

namespace App\Http\Controllers\Api;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\InventoryResource;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $inventories = Inventory::getOrPaginate();
        return InventoryResource::collection($inventories);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unity_id' => 'required|exists:unities,id',
            'type' => 'nullable|string|max:255', 
            'has_order_pending' => 'boolean',
        ]);

        $inventory = Inventory::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'unity_id' => $data['unity_id'],
            'type' => $data['type'] ?? 0,
            'has_order_pending' => false,
            'items' => json_encode([]),
            'operations' => json_encode([]),
            'orders' => json_encode([]),

        ]);

        return new InventoryResource($inventory);
    }

    public function storeOrder(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.code' => 'required|string',
            'items.*.cant' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
        ]);

        // Transform items into associative array with unique ID
        $formattedItems = [];
        foreach ($data['items'] as $index => $item) {
            $formattedItems[$index] = [
                'item_index' => $index,
                'cant' => $item['cant'],
                'code' => $item['code'],
                'name' => $item['name']
            ];
        }

        // Add the order to the inventory
        $inventory->addOrder($formattedItems);

        return new InventoryResource($inventory);
    }

    public function updateOrder(Request $request, Inventory $inventory, $orderIndex)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.code' => 'required|string',
            'items.*.cant' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
        ]);
        // Transform items into associative array with unique ID
        $formattedItems = [];
        foreach ($data['items'] as $index => $item) {
            $formattedItems[$index] = [
                'item_index' => $index,
                'cant' => $item['cant'],
                'code' => $item['code'],
                'name' => $item['name']
            ];
        }
        

        // Update the order in the inventory
        $inventory->updateOrder($orderIndex, $formattedItems);

        return new InventoryResource($inventory);
    }

    public function deleteOrder(Request $request, Inventory $inventory, $orderIndex)
    {
        // Delete the order from the inventory
        $inventory->deleteOrder($orderIndex);

        return new InventoryResource($inventory);
    }

    public function changeOrderState(Request $request, Inventory $inventory, $orderIndex)
    {
        
        $states = ['created', 'checked', 'ordered', 'received', 'cancelled'];
        $data = $request->validate([
            'state' => 'sometimes|required|string|in:' . implode(',', $states),
        ]);

        $currentState = $inventory->getOrderState($orderIndex);

        $states = ['created', 'checked', 'ordered', 'received'];

        $currentStateIndex = array_search($currentState, $states);

        if ($currentState == 'received' || $currentState == 'cancelled') {
            return response()->json(['error' => 'Invalid current state'], 400);
        }
        
        $nextState = $states[$currentStateIndex + 1] ?? null;
        // Change the order state
        $inventory->changeOrderState($orderIndex, $data['state'] ?? $nextState);

        if ($data['state'] ?? $nextState === 'received') {
            // If the order state is changed to 'received', apply the order to the inventory
            $operationIndex = $inventory->setOrdertoOperations($orderIndex);
            $inventory->applyOperation($operationIndex);
            $inventory->updateHasOrderPendingByState();
        }

        return new InventoryResource($inventory);
    }

    public function orderToOperation(Request $request, Inventory $inventory, $orderIndex)
    {
        // Convert the order to an operation
        $inventory->setOrdertoOperations($orderIndex);

        return new InventoryResource($inventory);
    }

    public function storeOperation(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'type' => 'required|string|in:inlet,outlet',
            'items' => 'required|array',
            'items.*.code' => 'required|string',
            'items.*.cant' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
        ]);

        foreach ($data['items'] as $index => $item) {
            $formattedItems[$index] = [
                'item_index' => $index,
                'cant' => $item['cant'],
                'code' => $item['code'],
                'name' => $item['name']
            ];
        }

        // Add the operation to the inventory
        $inventory->addOperation($formattedItems, $data['type']);

        return new InventoryResource($inventory);
    }

    

    public function updateOperation(Request $request, Inventory $inventory, $operationIndex)
    {
        $data = $request->validate([
            'type' => 'required|string|in:inlet,outlet',
            'items' => 'required|array',
            'items.*.code' => 'required|string',
            'items.*.cant' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
        ]);

        // Update the operation in the inventory
        $inventory->updateOperation($operationIndex, $data['items'], $data['type']);

        
        return new InventoryResource($inventory);
    }

    public function addOperation(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'type' => 'sometimes|required|string|in:inlet,outlet',
            'items' => 'required|array',
            'items.*.code' => 'required|string',
            'items.*.cant' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
        ]);

        // Add the operation to the inventory
        $inventory->addOperation($data['items'], $data['type']);

        return new InventoryResource($inventory);
    }

    public function applyOperation(Request $request, Inventory $inventory, $operationIndex)
    {
        // Apply the operation to the inventory
        $inventory->applyOperation($operationIndex);

        return new InventoryResource($inventory);
    }

    public function deleteOperation(Request $request, Inventory $inventory, $operationIndex)
    {
        // Delete the operation from the inventory
        $inventory->deleteOperation($operationIndex);

        return new InventoryResource($inventory);
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        //
        return new InventoryResource($inventory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        //
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unity_id' => 'required|exists:unities,id',
            'type' => 'nullable|string|max:255', 
            'has_order_pending' => 'boolean',
        ]);

        $inventory->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'unity_id' => $data['unity_id'],
            'type' => $data['type'] ?? 0,
            'has_order_pending' => $data['has_order_pending'] ?? false,
        ]);
        return new InventoryResource($inventory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        //
        $inventory->delete();
        return response()->noContent();

    }


}
