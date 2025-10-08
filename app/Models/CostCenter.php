<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory, MultiTenant;

    protected $fillable = [
        'name', 'company_id',
    ];

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function getCostsInMonth()
    {
        $startMonth = Carbon::now()->startOfMonth()->toDateString();
        $endMonth = Carbon::now()->endOfMonth()->toDateString();

        $amount = $this->entries->where('paid_at', '!=', null)
            ->where('operation', 2)
            ->where('company_id', auth()->user()->company_id)
            ->whereBetween('paid_at', [$startMonth, $endMonth])
            ->pluck('value')
            ->sum();

        return $amount;
    }

    public function getCostsInYear($year)
    {
        $date = "$year-01-01";

        $startYear = Carbon::createFromFormat('Y-m-d', $date)->startOfYear()->toDateString();
        $endYear = Carbon::createFromFormat('Y-m-d', $date)->endOfYear()->toDateString();

        $amount = $this->entries->where('paid_at', '!=', null)
            ->where('operation', 2)
            ->where('company_id', auth()->user()->company_id)
            ->whereBetween('paid_at', [$startYear, $endYear])
            ->pluck('value')
            ->sum();

        return $amount;
    }
}
