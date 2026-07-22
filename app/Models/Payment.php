<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Guarda el resultado del pago sin almacenar credenciales completas de tarjeta. */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'method', 'status', 'provider_reference', 'last_four', 'amount', 'processed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    /** Pedido pagado por este registro. */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
