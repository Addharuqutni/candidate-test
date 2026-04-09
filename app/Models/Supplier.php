<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    /**
     * @return HasMany<Layup, $this>
     */
    public function layups(): HasMany
    {
        return $this->hasMany(Layup::class);
    }
}
