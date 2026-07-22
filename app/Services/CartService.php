<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const TAX_RATE = 0.13;

    public const SHIPPING_COST = 2500;

    public const FREE_SHIPPING_FROM = 30000;

    /** @return array<int, int> */
    public function raw(): array
    {
        return Session::get('cart', []);
    }

    public function add(Product $product, int $quantity = 1): void
    {
        if (! $product->is_active || $product->stock < 1) {
            throw ValidationException::withMessages(['product' => 'Este producto no está disponible.']);
        }

        $cart = $this->raw();
        $requested = ($cart[$product->id] ?? 0) + max(1, $quantity);

        if ($requested > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Solo hay {$product->stock} unidades disponibles.",
            ]);
        }

        $cart[$product->id] = $requested;
        Session::put('cart', $cart);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($product);

            return;
        }

        if (! $product->is_active || $quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "La cantidad disponible es {$product->stock}.",
            ]);
        }

        $cart = $this->raw();
        $cart[$product->id] = $quantity;
        Session::put('cart', $cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        Session::put('cart', $cart);
    }

    public function clear(): void
    {
        Session::forget('cart');
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /** @return Collection<int, array{product: Product, quantity: int, line_total: int}> */
    public function items(): Collection
    {
        $cart = $this->raw();
        $products = Product::query()->with('category')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)->map(function (int $quantity, int|string $productId) use ($products) {
            $product = $products->get((int) $productId);

            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $product->price * $quantity,
            ];
        })->filter()->values();
    }

    /** @return array{subtotal: int, tax: int, shipping: int, total: int} */
    public function totals(?Collection $items = null): array
    {
        $items ??= $this->items();
        $subtotal = (int) $items->sum('line_total');
        $tax = (int) round($subtotal * self::TAX_RATE);
        $shipping = $subtotal === 0 || $subtotal >= self::FREE_SHIPPING_FROM ? 0 : self::SHIPPING_COST;

        return compact('subtotal', 'tax', 'shipping') + ['total' => $subtotal + $tax + $shipping];
    }
}
