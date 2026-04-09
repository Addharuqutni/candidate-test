<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function paginateWithLayupCount(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Supplier::query()
            ->withCount('layups')
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Supplier
    {
        return Supplier::query()->findOrFail($id);
    }

    public function findWithRelationsOrFail(int $id): Supplier
    {
        return Supplier::query()
            ->with(['layups.layers' => fn ($query) => $query->orderBy('layer_order')])
            ->findOrFail($id);
    }

    public function create(array $attributes): Supplier
    {
        return Supplier::query()->create($attributes);
    }

    public function update(Supplier $supplier, array $attributes): bool
    {
        return $supplier->update($attributes);
    }

    public function delete(Supplier $supplier): bool
    {
        return $supplier->delete();
    }
}
