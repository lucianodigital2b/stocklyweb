<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenant;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, MultiTenant;

    protected $fillable = [
        'name',
        'status',
        'company_id'
    ];

    public function docks()
    {
        return $this->belongsToMany(Dock::class, 'warehouse_docks', 'warehouse_id');
    }

    public function docksRelation()
    {
        return $this->hasMany(WarehouseDock::class);
    }

    public function getDocasStrAttribute()
    {
        $countDocas = $this->docks->count();
        if(!$countDocas)
        {
            return null;
        }
        return $countDocas. ($countDocas == 1 ? ' doca': ' docas' );
    }

}
