<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Agrupa productos del catálogo y usa su slug legible en las URLs. */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    /** Productos que pertenecen a la categoría. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Laravel resolverá /categorias/{categoria} por slug en lugar del ID. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
