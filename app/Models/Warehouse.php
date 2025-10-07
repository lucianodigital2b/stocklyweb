<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'status'
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
