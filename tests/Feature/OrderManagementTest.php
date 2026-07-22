<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verifica el flujo administrativo y la restitución idempotente de inventario. */
class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelling_an_order_refunds_payment_and_restores_stock_once(): void
    {
        [$admin, $order, $product] = $this->orderFixture();

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('refunded', $order->payment->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_invalid_status_jump_is_rejected_without_side_effects(): void
    {
        [$admin, $order, $product] = $this->orderFixture();

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHasErrors('status');

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('approved', $order->payment->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_customer_cannot_change_order_status(): void
    {
        [, $order] = $this->orderFixture();
        $customer = User::factory()->create();

        $this->actingAs($customer)->patch(route('admin.orders.status', $order), ['status' => 'preparing'])
            ->assertForbidden();
        $this->assertSame('paid', $order->fresh()->status);
    }

    /** @return array{User, Order, Product} */
    private function orderFixture(): array
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $customer = User::factory()->create();
        $category = Category::create(['name' => 'Café', 'slug' => 'cafe']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Café prueba',
            'slug' => 'cafe-prueba',
            'description' => 'Producto para probar estados.',
            'price' => 5000,
            'stock' => 3,
            'is_active' => true,
        ]);
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'OT-TEST-001',
            'tracking_number' => 'CRPOST-TEST001',
            'status' => 'paid',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '8888-1212',
            'shipping_address' => 'San José, Costa Rica, dirección de prueba',
            'subtotal' => 10000,
            'tax' => 1300,
            'shipping' => 2500,
            'total' => 13800,
            'purchased_at' => now(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 5000,
            'quantity' => 2,
            'line_total' => 10000,
        ]);
        $order->payment()->create([
            'method' => 'card',
            'status' => 'approved',
            'provider_reference' => 'SIM-ORDER-TEST-001',
            'last_four' => '1111',
            'amount' => 13800,
            'processed_at' => now(),
        ]);

        return [$admin, $order, $product];
    }
}
