<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    public function createSupplier($data = [])
    {
        return $this->store($data);
    }

    public function store($data = [])
    {
        $supplier = isset($data['id']) && ! empty($data['id']) ? $this->get($data['id']) : new Supplier;

        if (! isset($data['id']) || empty($data['id'])) {
            $data['company_id'] = auth()->user()->company_id;
        }

        $supplier->fill($data);
        $supplier->save();

        return $supplier;
    }

    public function list($data = [])
    {
        $suppliers = new Supplier;

        if (isset($data['where'])) {
            foreach ($data['where'] as $where) {
                $suppliers = $suppliers->where($where['field'], $where['operator'], $where['value']);
            }
        }

        if (isset($data['q']) && ! empty($data['q'])) {
            $searchbleFields = Supplier::searchbleFields();

            foreach ($searchbleFields as $i => $searchbleField) {
                if ($i > 0) {
                    $suppliers = $suppliers->orWhere($searchbleField, 'LIKE', '%'.$data['q'].'%');
                } else {
                    $suppliers = $suppliers->where($searchbleField, 'LIKE', '%'.$data['q'].'%');
                }
            }
        }

        if (isset($data['orderBy']) && isset($data['order'])) {
            $suppliers = $suppliers->orderBy($data['orderBy'], $data['order']);
        }

        if (isset($data['limit'])) {
            return $suppliers->paginate($data['limit']);
        } else {
            return $suppliers->get();
        }
    }

    public function get($value, $field = 'id')
    {
        return Supplier::where($field, $value)->first();
    }

    public function getSupplier($id)
    {
        return $this->get($id);
    }

    public function updateSupplier($id, $data = [])
    {
        $data['id'] = $id;
        return $this->store($data);
    }

    public function deleteSupplier($id)
    {
        $supplier = $this->get($id);
        if ($supplier) {
            return $supplier->delete();
        }
        return false;
    }
}
