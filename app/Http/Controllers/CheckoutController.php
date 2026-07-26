<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\SimulatedPaymentGateway;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $validated = $request->validated();
        $cart = $this->cart->raw();

        if ($cart === []) {
            return redirect()->route('products.index')->with('error', 'El carrito está vacío.');
        }

        $order = DB::transaction(function () use ($request, $validated, $cart) {
            $cartLines = $this->buildLockedCartLines($cart);
            $totals = $this->cart->totals($cartLines);
            $purchasedAt = now();
            $order = $this->createOrder($request, $validated, $totals, $purchasedAt);

            $this->saveOrderLines($order, $cartLines);

            // La pasarela retorna solo la referencia y los últimos cuatro dígitos seguros para persistir.
            $order->payment()->create(
                $this->paymentGateway->authorize($validated, $totals['total'], $purchasedAt)
            );

            $request->user()->update([
                'phone' => $validated['customer_phone'],
                'address' => $validated['shipping_address'],
            ]);

            return $order;
        });

        $this->cart->clear();

        return redirect()->route('orders.confirmation', $order)->with('success', '¡Pago aprobado y pedido confirmado!');
    }

    /**
     * Relee y bloquea el inventario antes de cobrar para evitar sobreventas.
     *
     * @param  array<int, int>  $cart
     * @return Collection<int, array{product: Product, quantity: int, line_total: int}>
     */
    private function buildLockedCartLines(array $cart): Collection
    {
        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function (int $quantity, int|string $productId) use ($products) {
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
    }

    /**
     * Guarda la cabecera del pedido con los datos confirmados de la compra.
     *
     * @param  array<string, mixed>  $validated
     * @param  array{subtotal: int, tax: int, shipping: int, total: int}  $totals
     */
    private function createOrder(
        Request $request,
        array $validated,
        array $totals,
        CarbonInterface $purchasedAt,
    ): Order {
        return Order::create([
            'user_id' => $request->user()->id,
            'order_number' => 'CS-'.$purchasedAt->format('Ymd').'-'.Str::upper(Str::random(6)),
            'tracking_number' => 'CRPOST-'.Str::upper(Str::random(10)),
            'status' => 'paid',
            'customer_name' => $validated['customer_name'],
            'customer_email' => $request->user()->email,
            'customer_phone' => $validated['customer_phone'],
            'shipping_address' => $validated['shipping_address'],
            ...$totals,
            'purchased_at' => $purchasedAt,
        ]);
    }

    /**
     * Guarda cada producto comprado y descuenta sus unidades del inventario.
     *
     * @param  Collection<int, array{product: Product, quantity: int, line_total: int}>  $cartLines
     */
    private function saveOrderLines(Order $order, Collection $cartLines): void
    {
        foreach ($cartLines as $cartLine) {
            $order->items()->create([
                'product_id' => $cartLine['product']->id,
                'product_name' => $cartLine['product']->name,
                'unit_price' => $cartLine['product']->price,
                'quantity' => $cartLine['quantity'],
                'line_total' => $cartLine['line_total'],
            ]);

            $cartLine['product']->decrement('stock', $cartLine['quantity']);
        }
    }

    /** Muestra la confirmación únicamente al dueño del pedido o a un administrador. */
    public function confirmation(Request $request, Order $order)
    {
        abort_unless($request->user()->id === $order->user_id || $request->user()->is_admin, 403);

        return view('checkout.confirmation', ['order' => $order->load('items', 'payment')]);
    }
}
