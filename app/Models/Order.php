<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Cabecera histórica de una compra, incluidos cliente, dirección y totales. */
class Order extends Model
{
    use HasFactory;

    /** Lista blanca de estados aceptados por el panel administrativo. */
    public const STATUSES = ['paid', 'preparing', 'shipped', 'delivered', 'cancelled'];

    /** Flujo permitido para impedir saltos ilógicos o reaperturas accidentales. */
    public const STATUS_TRANSITIONS = [
        'paid' => ['preparing', 'cancelled'],
        'preparing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'user_id', 'order_number', 'tracking_number', 'status', 'customer_name',
        'customer_email', 'customer_phone', 'shipping_address', 'subtotal',
        'tax', 'shipping', 'total', 'purchased_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['purchased_at' => 'datetime'];
    }

    /** Cuenta que realizó el pedido. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Líneas de productos congeladas al momento de comprar. */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Resultado seguro del pago asociado. */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /** @return list<string> */
    public function allowedStatusTransitions(): array
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? [];
    }

    /** Comprueba el flujo sin confiar en el valor recibido desde el formulario. */
    public function canTransitionTo(string $status): bool
    {
        return $status === $this->status || in_array($status, $this->allowedStatusTransitions(), true);
    }

    /** Expone el número público del pedido en vez del ID interno. */
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}
