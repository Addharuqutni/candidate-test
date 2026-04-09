<?php

namespace App\Repositories\Eloquent;

use App\Models\Layup;
use App\Models\Supplier;
use App\Repositories\Contracts\LayupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LayupRepository implements LayupRepositoryInterface
{
    public function getBySupplier(Supplier $supplier): Collection
    {
        return $supplier->layups()
            ->withCount('layers')
            ->orderBy('name')
            ->get();
    }

    public function findForSupplierOrFail(Supplier $supplier, int $layupId): Layup
    {
        return $supplier->layups()
            ->with(['layers' => fn ($query) => $query->orderBy('layer_order')])
            ->findOrFail($layupId);
    }

    public function findByNameInSupplier(Supplier $supplier, string $name): ?Layup
    {
        return $supplier->layups()->where('name', $name)->first();
    }

    public function createForSupplier(Supplier $supplier, array $attributes): Layup
    {
        return $supplier->layups()->create($attributes);
    }

    public function update(Layup $layup, array $attributes): bool
    {
        return $layup->update($attributes);
    }

    public function delete(Layup $layup): bool
    {
        return $layup->delete();
    }
}
