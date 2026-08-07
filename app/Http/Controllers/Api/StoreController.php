<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\StoreResource;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $stores = Store::getOrPaginate();
        return StoreResource::collection($stores);
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
            'supplier' => 'nullable|string|max:255',
            'items' => 'nullable|array',
        ]);

        //dd($data);

        $store = Store::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'supplier' => $data['supplier'] ?? null,
            'items' => json_encode($data['items'] ?? [], JSON_FORCE_OBJECT),
        ]);

        return new StoreResource($store);

    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        return new StoreResource($store);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Store $store)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supplier' => 'nullable|string|max:255',
            'items' => 'nullable|array',
        ]);

        $store->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'supplier' => $data['supplier'] ?? null,
            'items' => json_encode($data['items'] ?? [], JSON_FORCE_OBJECT),
        ]);

        return new StoreResource($store);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        //
        $store->delete();
        return response()->json(['message' => 'Store deleted successfully']);
    }
}
