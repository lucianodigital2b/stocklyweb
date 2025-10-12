<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
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

        $warehouses = $this->warehouseService->list($args);
        
        return response()->json($warehouses);
    }

    /**
     * Store a new warehouse.
     *
     * @param  \App\Http\Requests\StoreWarehouseRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->warehouseService->create($request->validated());
        
        return response()->json([
            'message' => 'Warehouse created successfully',
            'data' => new WarehouseResource($warehouse)
        ], 201);
    }

    /**
     * Display the specified warehouse.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $warehouse = $this->warehouseService->find($id);
        
        return response()->json([
            'data' => new WarehouseResource($warehouse)
        ]);
    }

    /**
     * Update the specified warehouse.
     *
     * @param  \App\Http\Requests\UpdateWarehouseRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateWarehouseRequest $request, $id): JsonResponse
    {
        $warehouse = $this->warehouseService->update($id, $request->validated());
        
        return response()->json([
            'message' => 'Warehouse updated successfully',
            'data' => new WarehouseResource($warehouse)
        ]);
    }

    /**
     * Remove the specified warehouse.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->warehouseService->delete($id);
        
        return response()->json([
            'message' => 'Warehouse deleted successfully'
        ]);
    }
}