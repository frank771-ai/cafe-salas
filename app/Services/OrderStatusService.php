<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Aplica cambios de estado conservando inventario y pago consistentes. */
class OrderStatusService
{
    /**
     * Bloquea el pedido durante la actualización y devuelve inventario al cancelar.
     * Repetir el mismo estado es idempotente y nunca repone existencias dos veces.
     */
    public function transition(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => "No se puede pasar de {$lockedOrder->status} a {$newStatus}.",
                ]);
            }

            if ($lockedOrder->status === $newStatus) {
                return $lockedOrder;
            }

            if ($newStatus === 'cancelled') {
                foreach ($lockedOrder->items()->whereNotNull('product_id')->get() as $item) {
                    Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }

                $lockedOrder->payment()->update(['status' => 'refunded']);
            }

            $lockedOrder->update(['status' => $newStatus]);

            return $lockedOrder->refresh();
        });
    }
}
