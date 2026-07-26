<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verifica compra, validación de pagos, inventario y autorización de facturas. */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_customer_can_pay_by_card_and_receives_tracking_number(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Laura Jiménez',
                'customer_phone' => '8888-1212',
                'shipping_address' => 'San Pedro, Montes de Oca, casa número 10',
                'payment_method' => 'card',
                'card_holder' => 'Laura Jiménez',
                'card_number' => '4111 1111 1111 1111',
                'card_expiry' => now()->addYear()->format('m/y'),
                'card_cvv' => '123',
            ])->assertRedirect();

        $order = $user->orders()->with('payment')->firstOrFail();
        $this->assertStringStartsWith('CS-', $order->order_number);
        $this->assertStringStartsWith('CRPOST-', $order->tracking_number);
        $this->assertSame(11992, $order->total);
        $this->assertSame('1111', $order->payment->last_four);
        $this->assertDatabaseMissing('payments', ['provider_reference' => '4111111111111111']);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertEmpty(session('cart', []));
    }

    public function test_paypal_checkout_requires_a_valid_email(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Laura Jiménez',
                'customer_phone' => '8888-1212',
                'shipping_address' => 'San Pedro, Montes de Oca, casa número 10',
                'payment_method' => 'paypal',
                'paypal_email' => 'correo-invalido',
            ])->assertSessionHasErrors('paypal_email');
    }

    public function test_card_checkout_rejects_a_number_that_fails_luhn_validation(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Laura Jiménez',
                'customer_phone' => '8888-1212',
                'shipping_address' => 'San Pedro, Montes de Oca, casa número 10',
                'payment_method' => 'card',
                'card_holder' => 'Laura Jiménez',
                'card_number' => '4111 1111 1111 1112',
                'card_expiry' => now()->addYear()->format('m/y'),
                'card_cvv' => '123',
            ])->assertSessionHasErrors('card_number');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_customer_cannot_open_another_users_invoice(): void
    {
        $this->seed();
        $owner = User::where('email', 'cliente@cafesalas.test')->firstOrFail();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('orders.invoice', $owner->orders()->first()))->assertForbidden();
    }

    public function test_invalid_card_data_is_never_flashed_back_to_the_session(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), [
                'customer_name' => 'Laura Jiménez',
                'customer_phone' => '8888-1212',
                'shipping_address' => 'San Pedro, Montes de Oca, casa número 10',
                'payment_method' => 'card',
                'card_holder' => 'Laura Jiménez',
                'card_number' => '4111 1111 1111 1112',
                'card_expiry' => '01/20',
                'card_cvv' => '123',
            ])->assertRedirect(route('checkout.create'))
            ->assertSessionMissing('_old_input.card_holder')
            ->assertSessionMissing('_old_input.card_number')
            ->assertSessionMissing('_old_input.card_expiry')
            ->assertSessionMissing('_old_input.card_cvv');
    }

    public function test_expired_card_is_rejected_without_creating_an_order(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Laura Jiménez',
                'customer_phone' => '8888-1212',
                'shipping_address' => 'San Pedro, Montes de Oca, casa número 10',
                'payment_method' => 'card',
                'card_holder' => 'Laura Jiménez',
                'card_number' => '4111 1111 1111 1111',
                'card_expiry' => now()->subYear()->format('m/y'),
                'card_cvv' => '123',
            ])->assertSessionHasErrors('card_expiry');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_inventory_change_rolls_back_the_entire_checkout(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 5]]);
        $product->update(['stock' => 4]);

        $this->post(route('checkout.store'), [
            'customer_name' => 'Laura Jiménez',
            'customer_phone' => '8888-1212',
            'shipping_address' => 'San Pedro, Montes de Oca, casa número 10',
            'payment_method' => 'paypal',
            'paypal_email' => ' LAURA@EXAMPLE.TEST ',
        ])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_paypal_checkout_succeeds_without_storing_card_data(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => ' Laura Jiménez ',
                'customer_phone' => ' 8888-1212 ',
                'shipping_address' => ' San Pedro, Montes de Oca, casa número 10 ',
                'payment_method' => 'paypal',
                'paypal_email' => ' LAURA@EXAMPLE.TEST ',
            ])->assertRedirect();

        $payment = $user->orders()->firstOrFail()->payment;
        $this->assertSame('paypal', $payment->method);
        $this->assertNull($payment->last_four);
        $this->assertSame('Laura Jiménez', $user->orders()->first()->customer_name);
    }

    public function test_guest_cannot_open_or_submit_checkout(): void
    {
        $this->get(route('checkout.create'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'))->assertRedirect(route('login'));
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Café', 'slug' => 'cafe']);

        return Product::create([
            'category_id' => $category->id, 'name' => 'Café prueba', 'slug' => 'cafe-prueba',
            'description' => 'Café para probar el proceso.', 'price' => 4200, 'stock' => 5,
            'image' => 'images/products/cafe-tarrazu.svg', 'featured' => true, 'is_active' => true,
        ]);
    }
}
