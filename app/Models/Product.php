<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Representa un artículo vendible con precio entero, inventario y categoría. */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'stock',
        'image', 'featured', 'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['featured' => 'boolean', 'is_active' => 'boolean'];
    }

    /** Categoría propietaria del producto. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Reutiliza la condición que oculta productos desactivados del escaparate. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Usa URLs como /productos/tarrazu-reserva-340g. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
