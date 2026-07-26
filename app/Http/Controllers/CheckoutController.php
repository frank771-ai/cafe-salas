<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\SimulatedPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Coordina el checkout: valida el carrito, reserva inventario y crea pedido y pago.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly SimulatedPaymentGateway $paymentGateway,
    ) {}

    /** Muestra el resumen de compra con los datos actuales del usuario. */
    public function create(Request $request)
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Agregue productos antes de continuar.');
        }

        return view('checkout.create', [
            'items' => $items,
            'totals' => $this->cart->totals($items),
            'user' => $request->user(),
        ]);
    }

    /**
     * Confirma la compra dentro de una transacción para mantener consistentes
     * el inventario, el pedido, sus líneas y el registro del pago.
     */
    public function store(CheckoutRequest $request)
    {
        $data = $request->validated();
        $cart = $this->cart->raw();

        if ($cart === []) {
            return redirect()->route('products.index')->with('error', 'El carrito está vacío.');
        }

        $order = DB::transaction(function () use ($request, $data, $cart) {
            // Se vuelve a consultar y bloquear el inventario para evitar vender más unidades de las disponibles.
            $products = Product::query()->whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            $items = collect($cart)->map(function (int $quantity, int|string $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product || ! $product->is_active || $quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'El inventario cambió. Revise las cantidades del carrito e intente de nuevo.',
                    ]);
                }

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $product->price * $quantity,
                ];
            })->values();

            $totals = $this->cart->totals($items);
            $now = now();
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'CS-'.$now->format('Ymd').'-'.Str::upper(Str::random(6)),
                'tracking_number' => 'CRPOST-'.Str::upper(Str::random(10)),
                'status' => 'paid',
                'customer_name' => $data['customer_name'],
                'customer_email' => $request->user()->email,
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                ...$totals,
                'purchased_at' => $now,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit_price' => $item['product']->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);
                $item['product']->decrement('stock', $item['quantity']);
            }

            // La pasarela retorna solo la referencia y los últimos cuatro dígitos seguros para persistir.
            $order->payment()->create(
                $this->paymentGateway->authorize($data, $totals['total'], $now)
            );

            $request->user()->update([
                'phone' => $data['customer_phone'],
                'address' => $data['shipping_address'],
            ]);

            return $order;
        });

        $this->cart->clear();

        return redirect()->route('orders.confirmation', $order)->with('success', '¡Pago aprobado y pedido confirmado!');
    }

    /** Muestra la confirmación únicamente al dueño del pedido o a un administrador. */
    public function confirmation(Request $request, Order $order)
    {
        abort_unless($request->user()->id === $order->user_id || $request->user()->is_admin, 403);

        return view('checkout.confirmation', ['order' => $order->load('items', 'payment')]);
    }
}
