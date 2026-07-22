<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

/** Traduce las acciones HTTP del carrito a operaciones del CartService. */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    /** Presenta los productos y totales calculados en la sesión. */
    public function index()
    {
        $items = $this->cart->items();

        return view('cart.index', ['items' => $items, 'totals' => $this->cart->totals($items)]);
    }

    /** Agrega una cantidad validada de un producto disponible. */
    public function store(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $this->cart->add($product, $data['quantity']);

        return redirect()->route('cart.index')->with('success', "{$product->name} se agregó al carrito.");
    }

    /** Actualiza la cantidad; el valor cero elimina el producto. */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);
        $this->cart->update($product, $data['quantity']);

        return back()->with('success', 'Cantidad actualizada.');
    }

    /** Elimina explícitamente una línea del carrito. */
    public function destroy(Product $product)
    {
        $this->cart->remove($product);

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
