<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index()
    {
        $items = $this->cart->items();

        return view('cart.index', ['items' => $items, 'totals' => $this->cart->totals($items)]);
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $this->cart->add($product, $data['quantity']);

        return redirect()->route('cart.index')->with('success', "{$product->name} se agregó al carrito.");
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);
        $this->cart->update($product, $data['quantity']);

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function destroy(Product $product)
    {
        $this->cart->remove($product);

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
