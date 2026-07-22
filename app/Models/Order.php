<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['paid', 'preparing', 'shipped', 'delivered', 'cancelled'];

    protected $fillable = [
        'user_id', 'order_number', 'tracking_number', 'status', 'customer_name',
        'customer_email', 'customer_phone', 'shipping_address', 'subtotal',
        'tax', 'shipping', 'total', 'purchased_at',
    ];

    protected function casts(): array
    {
        return ['purchased_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}
