<?php

namespace App\Services;

use App\Models\CostCenter;

class CostCenterService  
{
    public function store($data = [])
    {
        $costCenter = isset($data['id']) && ! empty($data['id']) ? CostCenter::find($data['id']) : new CostCenter;

        $costCenter->name = $data['name'];
        $costCenter->company_id = auth()->user()->company_id;

        $costCenter->save();

        return $costCenter;
    }

    public function list($data = [], $user = null)
    {
        $authUser = auth()->user();
        $costCenter = new CostCenter;

        $costCenter = $costCenter->where('company_id', $authUser->company_id);

        if (isset($data['q']) && ! empty($data['q'])) {
            $costCenter = $costCenter->where('name', 'LIKE', '%'.$data['q'].'%');
        }

        if (isset($data['limit'])) {
            return $costCenter->paginate($data['limit']);
        }

        return $costCenter->get();
    }

    public function createCostCenter($data = [])
    {
        $costCenter = new CostCenter;
        $costCenter->name = $data['name'];
        $costCenter->company_id = auth()->user()->company_id;
        $costCenter->save();

        return $costCenter;
    }

    public function getCostCenter($id)
    {
        return CostCenter::where('company_id', auth()->user()->company_id)
                        ->findOrFail($id);
    }

    public function updateCostCenter($id, $data = [])
    {
        $costCenter = CostCenter::where('company_id', auth()->user()->company_id)
                               ->findOrFail($id);
        
        $costCenter->name = $data['name'];
        $costCenter->save();

        return $costCenter;
    }

    public function deleteCostCenter($id)
    {
        $costCenter = CostCenter::where('company_id', auth()->user()->company_id)
                               ->findOrFail($id);
        
        return $costCenter->delete();
    }
}
