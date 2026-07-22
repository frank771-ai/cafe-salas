<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Conserva producto, cantidad y precio tal como figuraron en la compra. */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'unit_price', 'quantity', 'line_total',
    ];

    /** Pedido al que pertenece la línea. */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Producto original, si todavía existe en el catálogo. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
