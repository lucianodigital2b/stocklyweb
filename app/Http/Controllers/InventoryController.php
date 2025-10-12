<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $args = [
            'per_page' => $request->input('per_page', 10)
        ];
        
        if(!empty($request->input('q'))){
            $args['q'] = $request->input('q');
        }

        if(!empty($request->input('warehouse_id'))){
            $args['warehouse_id'] = $request->input('warehouse_id');
        }

        if(!empty($request->input('product_id'))){
            $args['product_id'] = $request->input('product_id');
        }

        $inventories = $this->inventoryService->list($args);
        
        return response()->json($inventories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInventoryRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInventoryRequest $request): JsonResponse
    {
        $inventory = $this->inventoryService->create($request->validated());
        
        return response()->json([
            'message' => 'Inventory created successfully',
            'data' => new InventoryResource($inventory)
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $inventory = $this->inventoryService->find($id);
        
        return response()->json([
            'data' => new InventoryResource($inventory)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventoryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateInventoryRequest $request, $id): JsonResponse
    {
        $inventory = $this->inventoryService->update($id, $request->validated());
        
        return response()->json([
            'message' => 'Inventory updated successfully',
            'data' => new InventoryResource($inventory)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->inventoryService->delete($id);
        
        return response()->json([
            'message' => 'Inventory deleted successfully'
        ], 204);
    }
}
