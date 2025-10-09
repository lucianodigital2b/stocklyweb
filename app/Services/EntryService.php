<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Log;
use App\Services\Asaas\Asaas;

class EntryService
{
    public function list($data = [])
    {
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

        if (isset($data['s']) && ! empty($data['s'])) {
            $searchbleFields = ['observations'];

            foreach ($searchbleFields as $i => $searchbleField) {
                if ($i > 0) {
                    $entries = $entries->orWhere($searchbleField, 'LIKE', '%'.$data['s'].'%');
                } else {
                    $entries = $entries->where($searchbleField, 'LIKE', '%'.$data['s'].'%');
                }
            }
        }

        if (isset($data['orderBy']) && isset($data['order'])) {
            $entries = $entries->orderBy($data['orderBy'], $data['order']);
        }

        if (isset($data['limit'])) {
            return $entries->paginate($data['limit']);
        } else {
            return $entries->get();
        }
    }

    public function createEntry($data)
    {
        $entry = isset($data['id']) && ! empty($data['id']) ? $this->get($data['id']) : new Entry;

        if (! isset($data['id']) || empty($data['id'])) {
            $data['company_id'] = auth()->user()->company_id;
        }

        $data['value'] = isset($data['id']) && ! empty($data['id']) ? $entry->value : $data['value'];

        $paid_at = $entry->paid_at;

        $entry->fill($data);

        $saved = $entry->save();

        if ($paid_at == null && isset($data['paid_at']) && $data['paid_at'] != null) {
            $data = [
                'company_id' => $entry->company_id,
                'user_id' => auth()->user()?->id,
                'customer_id' => $entry?->service?->customer?->id,
                'description' => $entry?->service ? "Lançamento #$entry->id ({$entry->service->name}) foi marcado como pago" : "Lançamento #$entry->id foi marcado como pago",
                'entity' => Entry::class,
                'action' => 'update',
                'user_name' => auth()->user()?->name,
            ];

        }

        if ($saved && isset($data['generate_bank_slip']) && $data['generate_bank_slip'] == 'on' && empty($entry->external_code)) {
            $asaasService = new Asaas;

            $asaasService->setPaymentMethod($entry->payment_method);
            $asaasService->pay($entry);
        }

        return $entry;
    }

    public function get($value, $field = 'id')
    {
        return Entry::where($field, $value)->first();
    }
}
