<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Inventory;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Requests\UpdateStockMovementRequest;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Get stock movements for a specific inventory
     */
    public function getByInventory($inventoryId)
    {
        $inventory = Inventory::with(['product', 'warehouse'])->findOrFail($inventoryId);
        
        $movements = StockMovement::with(['user'])
            ->where('inventory_id', $inventoryId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'movement_type' => $movement->movement_type,
                    'quantity_change' => $movement->quantity_change ?? 0,
                    'stock_before' => $movement->stock_before,
                    'stock_after' => $movement->stock_after,
                    'is_infinite_before' => $movement->is_infinite_before,
                    'is_infinite_after' => $movement->is_infinite_after,
                    'user_name' => $movement->user ? $movement->user->name : 'Sistema',
                    'created_at' => $movement->created_at->format('d/m/Y H:i:s'),
                    'created_at_iso' => $movement->created_at->toISOString(),
                ];
            });

        return response()->json([
            'inventory' => [
                'id' => $inventory->id,
                'product_name' => $inventory->product->name,
                'product_sku' => $inventory->product->sku,
                'warehouse_name' => $inventory->warehouse->name,
                'current_stock' => $inventory->stock,
                'is_infinite' => $inventory->is_infinite,
            ],
            'movements' => $movements
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockMovementRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockMovementRequest $request, StockMovement $stockMovement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockMovement $stockMovement)
    {
        //
    }
}
