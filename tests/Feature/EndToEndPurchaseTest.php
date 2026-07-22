<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Recorre como un usuario real el flujo completo desde registro hasta factura. */
class EndToEndPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_complete_the_full_purchase_journey(): void
    {
        $category = Category::create(['name' => 'Café', 'slug' => 'cafe']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Tarrazú de lanzamiento',
            'slug' => 'tarrazu-lanzamiento',
            'description' => 'Producto usado para validar todo el recorrido.',
            'price' => 8000,
            'stock' => 10,
            'image' => 'images/products/cafe-tarrazu.svg',
            'featured' => true,
            'is_active' => true,
        ]);

        $this->get(route('register'))->assertOk()->assertSee('Crear cuenta');
        $this->post(route('register.store'), [
            'name' => 'Cliente Lanzamiento',
            'email' => 'launch@example.test',
            'phone' => '8888-0000',
            'password' => 'Mercado123!',
            'password_confirmation' => 'Mercado123!',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Tarrazú de lanzamiento')
            ->assertCookie('recent_products');
        $this->post(route('cart.store', $product), ['quantity' => 2])
            ->assertRedirect(route('cart.index'));
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('₡16.000')
            ->assertSee('₡20.580');
        $this->get(route('checkout.create'))->assertOk()->assertSee('Finalizar compra');

        $this->post(route('checkout.store'), [
            'customer_name' => 'Cliente Lanzamiento',
            'customer_phone' => '8888-0000',
            'shipping_address' => 'San José, Costa Rica, dirección exacta de lanzamiento',
            'payment_method' => 'paypal',
            'paypal_email' => 'launch@example.test',
        ])->assertRedirect();

        $user = User::firstWhere('email', 'launch@example.test');
        $order = $user->orders()->with('payment')->firstOrFail();
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame('approved', $order->payment->status);

        $this->get(route('orders.confirmation', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($order->tracking_number);
        $this->get(route('orders.invoice', $order))->assertOk()->assertSee('₡20.580');
        $this->get(route('orders.invoice.pdf', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('profile.show'))->assertOk()->assertSee($order->order_number);
    }
}
