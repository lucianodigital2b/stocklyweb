<?php

namespace App\Models;

use App\Helpers\Formatting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseDock extends Model
{
    use HasFactory;

    protected $fillable = [
        'dock_id',
        'processing_days',
        'processing_seconds',
        'extra_fee'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function dock()
    {
        return $this->belongsTo(Dock::class);
    }

    public function getProcessingHoursAttribute()
    {
        return Formatting::secondsToInputTime($this->processing_seconds);
    }

}
