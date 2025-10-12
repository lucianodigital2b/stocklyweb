<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Get paginated list of inventories with optional filters.
     *
     * @param array $args
     * @return LengthAwarePaginator
     */
    public function list(array $args = []): LengthAwarePaginator
    {
        $query = Inventory::with(['warehouse', 'product']);

        // Apply search filter
        if (!empty($args['q'])) {
            $searchTerm = $args['q'];
            $query->whereHas('product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            })->orWhereHas('warehouse', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by warehouse
        if (!empty($args['warehouse_id'])) {
            $query->where('warehouse_id', $args['warehouse_id']);
        }

        // Filter by product
        if (!empty($args['product_id'])) {
            $query->where('product_id', $args['product_id']);
        }

        // Add company filter if user is authenticated
        if (auth()->check()) {
            $query->whereHas('warehouse', function ($q) {
                $q->where('company_id', auth()->user()->company_id);
            });
        }

        return $query->orderBy('id', 'desc')
                    ->paginate($args['per_page'] ?? 10);
    }

    /**
     * Find an inventory by ID.
     *
     * @param int $id
     * @return Inventory
     */
    public function find($id): Inventory
    {
        return Inventory::with(['warehouse', 'product', 'inventory_updates'])->findOrFail($id);
    }

    /**
     * Create a new inventory record.
     *
     * @param array $data
     * @return Inventory
     */
    public function create(array $data): Inventory
    {
        DB::beginTransaction();
        
        try {

            $data['warehouse_id'] = Warehouse::where('company_id', auth()->user()->company_id)->value('id');
            
            // Check if inventory already exists for this product-warehouse combination
            $existingInventory = Inventory::where('product_id', $data['product_id'])
                                        ->where('warehouse_id', $data['warehouse_id'])
                                        ->first();

            if ($existingInventory) {
                throw new \Exception('Inventory already exists for this product in this warehouse.');
            }

            $inventory = Inventory::create([
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $data['product_id'],
                'stock' => $data['stock'] ?? 0,
                'is_infinite' => $data['is_infinite'] ?? false,
            ]);

            DB::commit();
            
            return $inventory->load(['warehouse', 'product']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating inventory: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing inventory record.
     *
     * @param int $id
     * @param array $data
     * @return Inventory
     */
    public function update($id, array $data): Inventory
    {
        DB::beginTransaction();
        
        try {
            $inventory = Inventory::findOrFail($id);
            
            $inventory->update([
                'stock' => $data['stock'] ?? $inventory->stock,
                'is_infinite' => $data['is_infinite'] ?? $inventory->is_infinite,
            ]);

            DB::commit();
            
            return $inventory->load(['warehouse', 'product']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating inventory: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an inventory record.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id): bool
    {
        DB::beginTransaction();
        
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();

            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting inventory: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get inventory for a specific product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @return Inventory|null
     */
    public function getByProductAndWarehouse($productId, $warehouseId): ?Inventory
    {
        return Inventory::where('product_id', $productId)
                       ->where('warehouse_id', $warehouseId)
                       ->with(['warehouse', 'product'])
                       ->first();
    }

    /**
     * Update stock quantity for an inventory.
     *
     * @param int $id
     * @param int $quantity
     * @param string $operation ('add' or 'subtract')
     * @return Inventory
     */
    public function updateStock($id, $quantity, $operation = 'add'): Inventory
    {
        DB::beginTransaction();
        
        try {
            $inventory = Inventory::findOrFail($id);
            
            if ($operation === 'add') {
                $inventory->stock += $quantity;
            } elseif ($operation === 'subtract') {
                if ($inventory->stock < $quantity && !$inventory->is_infinite) {
                    throw new \Exception('Insufficient stock available.');
                }
                $inventory->stock -= $quantity;
            }

            $inventory->save();

            DB::commit();
            
            return $inventory->load(['warehouse', 'product']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating stock: ' . $e->getMessage());
            throw $e;
        }
    }
}