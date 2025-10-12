<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WarehouseDock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseService
{
    public function list($data = []) 
    {
        $warehouses = Warehouse::query();

        if (isset($data['q']) && !empty($data['q'])) {   
            $warehouses->where('name', 'LIKE', '%'. $data['q'] . '%');
        }

        if (isset($data['status'])) {
            $warehouses->where('status', $data['status']);
        }

        $warehouses->with(['docks', 'docksRelation']);

        if (isset($data['per_page'])) {
            return $warehouses->paginate($data['per_page']);
        }

        return $warehouses->get();
    }

    public function find($id)
    {
        return Warehouse::with(['docks', 'docksRelation'])->findOrFail($id);
    }

    public function create($data)
    {
        DB::beginTransaction();
        
        try {
            $warehouse = Warehouse::create([
                'name' => $data['name'],
                'status' => $data['status'] ?? 1,
                'company_id' => auth()->user()->company_id ?? 1
            ]);

            // Handle dock relationships if provided
            if (isset($data['docks']) && is_array($data['docks'])) {
                $this->syncDocks($warehouse, $data['docks']);
            }

            DB::commit();
            
            return $warehouse->load(['docks', 'docksRelation']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating warehouse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();
        
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            $warehouse->update([
                'name' => $data['name'],
                'status' => $data['status'] ?? $warehouse->status,
            ]);

            // Handle dock relationships if provided
            if (isset($data['docks']) && is_array($data['docks'])) {
                $this->syncDocks($warehouse, $data['docks']);
            }

            DB::commit();
            
            return $warehouse->load(['docks', 'docksRelation']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating warehouse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            // Delete related warehouse docks
            $warehouse->docksRelation()->delete();
            
            // Delete the warehouse
            $warehouse->delete();
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting warehouse: ' . $e->getMessage());
            throw $e;
        }
    }

    private function syncDocks($warehouse, $docks)
    {
        // Delete existing dock relationships
        $warehouse->docksRelation()->delete();

        // Create new dock relationships
        foreach ($docks as $dock) {
            WarehouseDock::create([
                'warehouse_id' => $warehouse->id,
                'dock_id' => $dock['dock_id'],
                'processing_days' => $dock['processing_days'] ?? 0,
                'processing_seconds' => $dock['processing_seconds'] ?? 0,
                'extra_fee' => $dock['extra_fee'] ?? 0,
            ]);
        }
    }
}