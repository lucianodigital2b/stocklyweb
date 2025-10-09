<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory, MultiTenant;

    protected $fillable = [
        'value', 
        'company_id', 
        'cost_center_id', 
        'supplier_id', 
        'due_at', 
        'paid_at',
        'operation', 
        'payment_method', 
        'observations', 
        'external_code', 
        'payment_info', 
        'account', 
        'barcode', 
        'charged_at',
        'order_id'

    ];

    protected $dates = [
        'due_at', 
        'paid_at',
    ];

    protected $casts = [
        'due_at' => 'date',
        'paid_at' => 'date',
    ];

    const FIELDS_LABEL = [
        'value' => 'Valor',
        'service_id' => 'Serviço',
        'cost_center_id' => 'Centro de custo',
        'supplier_id' => 'Fornecedor',
        'operation' => 'Operação',
        'payment_method' => 'Método de Pagamento',
        'observations' => 'Observações',
        'due_at' => 'Vencimento',
    ];

    const OPERATION_REVENUE = 1;

    const OPERATION_EXPENSE = 2;

    const OPERATION_TYPES = [
        '1' => 'Entrada',
        '2' => 'Saída',
    ];

    const PAYMENT_METHODS = [
        'pix' => 'PIX',
        'bank_slip' => 'BOLETO',
        'money' => 'DINHEIRO',
        'credit' => 'CRÉDITO',
        'debit' => 'DÉBITO',
    ];


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function getStatus()
    {
        if (! empty($this->paid_at)) {
            return 'Pago';
        }

        if (! empty($this->due_at) && $this->due_at->addDay(1)->lt(now())) {
            return 'Em atraso';
        }

        return 'Pendente';
    }

    public function getStatusClass()
    {
        if (! empty($this->paid_at)) {
            return 'success';
        }

        if (! empty($this->due_at) && $this->due_at->addDay(1)->lt(now())) {
            return 'danger';
        }

        return 'info';
    }

    public function getPaymentMethod()
    {
        return isset(self::PAYMENT_METHODS[$this->payment_method]) ? self::PAYMENT_METHODS[$this->payment_method] : '-';
    }
}
