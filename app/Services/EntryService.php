<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Log;
use App\Services\Asaas\Asaas;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class EntryService
{
    public function list($data = [])
    {
        try {
            $entries = new Entry;

            if (isset($data['where'])) {
                foreach ($data['where'] as $where) {
                    if ($where['operator'] == 'between') {
                        $entries = $entries->whereBetween($where['field'], $where['value']);
                    } else {
                        $entries = $entries->where($where['field'], $where['operator'], $where['value']);
                    }
                }
            }

            // Handle search parameter (both 's' and 'q' for compatibility)
            $searchTerm = $data['s'] ?? $data['q'] ?? null;
            if (!empty($searchTerm)) {
                $searchbleFields = ['observations', 'external_code', 'account', 'barcode'];

                foreach ($searchbleFields as $i => $searchbleField) {
                    if ($i > 0) {
                        $entries = $entries->orWhere($searchbleField, 'LIKE', '%'.$searchTerm.'%');
                    } else {
                        $entries = $entries->where($searchbleField, 'LIKE', '%'.$searchTerm.'%');
                    }
                }
            }

            // Handle created_at date filtering
            if (isset($data['created_at']) && !empty($data['created_at'])) {
                $entries = $entries->whereDate('created_at', $data['created_at']);
            }

            // Handle operation type filtering
            if (isset($data['operation']) && $data['operation'] !== null) {
                $entries = $entries->where('operation', $data['operation']);
            }

            if (isset($data['orderBy']) && isset($data['order'])) {
                $entries = $entries->orderBy($data['orderBy'], $data['order']);
            } else {
                // Default ordering by created_at desc
                $entries = $entries->orderBy('created_at', 'desc');
            }

            // Include relationships
            $entries = $entries->with(['supplier', 'costCenter', 'order.customer']);

            if (isset($data['limit'])) {
                return $entries->paginate($data['limit']);
            } else {
                return $entries->get();
            }
        } catch (\Exception $e) {
            throw new \Exception('Error retrieving entries: ' . $e->getMessage());
        }
    }

    public function createEntry($data)
    {
        try {
            $this->validateEntryData($data);
            
            $entry = new Entry;
            $data['company_id'] = auth()->user()->company_id;

            $entry->fill($data);
            $saved = $entry->save();

            if (!$saved) {
                throw new \Exception('Failed to save entry');
            }

            if ($saved && isset($data['generate_bank_slip']) && $data['generate_bank_slip'] == 'on' && empty($entry->external_code)) {
                $asaasService = new Asaas;
                $asaasService->setPaymentMethod($entry->payment_method);
                $asaasService->pay($entry);
            }

            return $entry;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Error creating entry: ' . $e->getMessage());
        }
    }

    public function updateEntry($id, $data)
    {
        try {
            $this->validateEntryData($data, $id);
            
            $entry = $this->get($id);
            
            if (!$entry) {
                throw new ModelNotFoundException('Entry not found');
            }

            // Store original paid_at value for comparison
            $originalPaidAt = $entry->paid_at;

            $entry->fill($data);
            $saved = $entry->save();

            if (!$saved) {
                throw new \Exception('Failed to update entry');
            }

            // Handle bank slip generation if needed
            if ($saved && isset($data['generate_bank_slip']) && $data['generate_bank_slip'] == 'on' && empty($entry->external_code)) {
                $asaasService = new Asaas;
                $asaasService->setPaymentMethod($entry->payment_method);
                $asaasService->pay($entry);
            }

            // Log payment status change if applicable
            if ($originalPaidAt == null && isset($data['paid_at']) && $data['paid_at'] != null) {
                // Entry was marked as paid - could add logging here if needed
            }

            return $entry;
        } catch (ValidationException $e) {
            throw $e;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Error updating entry: ' . $e->getMessage());
        }
    }

    public function deleteEntry($id)
    {
        try {
            $entry = $this->get($id);
            
            if (!$entry) {
                throw new ModelNotFoundException('Entry not found');
            }

            $deleted = $entry->delete();
            
            if (!$deleted) {
                throw new \Exception('Failed to delete entry');
            }

            return $deleted;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting entry: ' . $e->getMessage());
        }
    }

    public function get($value, $field = 'id')
    {
        try {
            return Entry::with(['supplier', 'costCenter', 'order'])->where($field, $value)->first();
        } catch (\Exception $e) {
            throw new \Exception('Error retrieving entry: ' . $e->getMessage());
        }
    }

    /**
     * Validate entry data
     */
    private function validateEntryData($data, $entryId = null)
    {
        $rules = [
            'value' => 'required|numeric|min:0.01',
            'operation' => 'required|in:1,2',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'order_id' => 'nullable|exists:orders,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'payment_method' => 'nullable|in:pix,bank_slip,money,credit,debit',
            'due_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'external_code' => 'nullable|string|max:255',
            'account' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:1000',
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Custom validation: ensure supplier_id is provided for expense operations
        if (isset($data['operation']) && $data['operation'] == Entry::OPERATION_EXPENSE && empty($data['supplier_id'])) {
            throw ValidationException::withMessages([
                'supplier_id' => ['Fornecedor é obrigatório para operações de saída.']
            ]);
        }

        // Custom validation: ensure order_id is provided for revenue operations
        if (isset($data['operation']) && $data['operation'] == Entry::OPERATION_REVENUE && empty($data['order_id'])) {
            throw ValidationException::withMessages([
                'order_id' => ['Pedido é obrigatório para operações de entrada.']
            ]);
        }
    }
}
