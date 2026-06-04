<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Like FranchiseTenantScope, but exams may be global (franchise_id NULL) when
 * authored centrally by Admin. A franchise_admin sees global exams plus any
 * exams owned by their own franchise.
 */
class ExamTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->hasRole('franchise_admin')) {
            $table = $model->getTable();
            $fid   = Auth::user()->franchise_id;

            $builder->where(function (Builder $q) use ($table, $fid) {
                $q->whereNull($table . '.franchise_id')
                  ->orWhere($table . '.franchise_id', $fid);
            });
        }
    }
}
