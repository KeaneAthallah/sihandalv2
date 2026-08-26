<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class Controller
{
    protected function applyOpdScope(Builder $query, ?User $user, string $column = 'opd_id'): Builder
    {
        if ($user !== null && ! $user->isAdmin()) {
            $query->where($column, $user->opd_id);
        }

        return $query;
    }

    protected function userOpds(?User $user): Collection
    {
        if ($user !== null && ! $user->isAdmin()) {
            return Opd::where('id', $user->opd_id)->orderBy('nama')->get();
        }

        return Opd::orderBy('nama')->get();
    }

    protected function authorizeOpdRecord(Model $record, ?User $user, string $column = 'opd_id'): void
    {
        if ($user !== null && ! $user->isAdmin() && $record->{$column} !== $user->opd_id) {
            abort(403);
        }
    }
}
