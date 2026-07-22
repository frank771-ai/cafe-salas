<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Cubre altas, cambios, eliminación y límites de inventario del carrito. */
class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_added_updated_and_removed(): void
    {
        $product = $this->product();

        $this->post(route('cart.store', $product), ['quantity' => 2])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->get(route('cart.index'))->assertOk()->assertSee('₡22.840');

        $this->patch(route('cart.update', $product), ['quantity' => 3])
            ->assertSessionHas('cart', [$product->id => 3]);

        $this->delete(route('cart.destroy', $product))
            ->assertSessionHas('cart', []);
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $product = $this->product(stock: 2);

        $this->from(route('products.show', $product))
            ->post(route('cart.store', $product), ['quantity' => 3])
            ->assertSessionHasErrors('quantity');
    }

    private function product(int $stock = 10): Product
    {
        $category = Category::create(['name' => 'Café', 'slug' => 'cafe']);

        return Product::create([
            'category_id' => $category->id, 'name' => 'Tarrazú', 'slug' => 'tarrazu',
            'description' => 'Café de prueba.', 'price' => 9000, 'stock' => $stock,
            'image' => 'images/products/cafe-tarrazu.svg', 'featured' => true, 'is_active' => true,
        ]);
    }
}
