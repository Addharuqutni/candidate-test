<?php

namespace App\Repositories\Eloquent;

use App\Models\Layer;
use App\Models\Layup;
use App\Repositories\Contracts\LayerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LayerRepository implements LayerRepositoryInterface
{
    public function getByLayup(Layup $layup): Collection
    {
        return $layup->layers()->orderBy('layer_order')->get();
    }

    public function findForLayupOrFail(Layup $layup, int $layerId): Layer
    {
        return $layup->layers()->findOrFail($layerId);
    }

    public function findByOrderInLayup(Layup $layup, int $layerOrder): ?Layer
    {
        return $layup->layers()->where('layer_order', $layerOrder)->first();
    }

    public function createForLayup(Layup $layup, array $attributes): Layer
    {
        return $layup->layers()->create($attributes);
    }

    public function update(Layer $layer, array $attributes): bool
    {
        return $layer->update($attributes);
    }

    public function delete(Layer $layer): bool
    {
        return $layer->delete();
    }
}
