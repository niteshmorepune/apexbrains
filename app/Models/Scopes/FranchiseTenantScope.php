<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class FranchiseTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->hasRole('franchise_admin')) {
            $builder->where($model->getTable() . '.franchise_id', Auth::user()->franchise_id);
        }
    }
}
