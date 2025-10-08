<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, MultiTenant;

    protected $fillable = [
        'name', 'email', 'phone', 'number', 'address', 'postcode', 'state', 'city', 'neighborhood',
        'cost_center_id', 'observations', 'company_id',
    ];

    public static function searchbleFields()
    {
        return ['name', 'email'];
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
}
