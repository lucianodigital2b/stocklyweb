<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class MultiTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Só aplica se o modelo NÃO for User (evita loop fatal)
        if ($model instanceof \App\Models\User) {
            return;
        }

        // Adia acesso ao Auth apenas após boot do app
        app()->booted(function () use ($builder, $model) {
            if (Auth::check() && !Auth::user()->isSuperAdmin()) {
                $builder->where($model->getTable() . '.company_id', Auth::user()->company_id);
            }
        });
    }
}

trait MultiTenant
{
    public static function bootMultiTenant()
    {
        static::addGlobalScope(new MultiTenantScope);

        static::creating(function ($model) {
            if (
                Auth::check() &&
                !Auth::user()->isSuperAdmin() &&
                !$model->company_id
            ) {
                $model->company_id = Auth::user()->company_id;
                
                // Only set store_id if the model has this attribute
                if (in_array('store_id', $model->getFillable()) || $model->hasAttribute('store_id')) {
                    $model->store_id = Store::where('company_id', Auth::user()->company_id)->first()->id;
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeAllTenants($query)
    {

        return $query->withoutGlobalScope(MultiTenantScope::class);
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->withoutGlobalScope(MultiTenantScope::class)
                    ->where($this->getTable() . '.company_id', $tenantId);
    }
}
