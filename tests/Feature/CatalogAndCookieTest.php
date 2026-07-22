<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndCookieTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_filtered_by_name_category_and_price(): void
    {
        $coffee = Category::create(['name' => 'Café', 'slug' => 'cafe']);
        $gifts = Category::create(['name' => 'Regalos', 'slug' => 'regalos']);
        $this->product($coffee, 'Tarrazú Reserva', 'tarrazu', 8500);
        $this->product($gifts, 'Caja Regalo', 'caja-regalo', 20000);

        $response = $this->get(route('products.index', [
            'q' => 'Tarrazú', 'category' => 'cafe', 'min_price' => 8000, 'max_price' => 9000,
        ]));

        $response->assertOk()->assertSee('Tarrazú Reserva')->assertDontSee('Caja Regalo');
    }

    public function test_visiting_a_product_creates_the_recent_products_cookie(): void
    {
        $category = Category::create(['name' => 'Café', 'slug' => 'cafe']);
        $product = $this->product($category, 'Poás Volcánico', 'poas', 7900);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Poás Volcánico')
            ->assertCookie('recent_products');
    }

    public function test_blade_escapes_product_content(): void
    {
        $category = Category::create(['name' => 'Café', 'slug' => 'cafe']);
        $product = $this->product($category, '<script>alert(1)</script>', 'seguro', 5000);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    private function product(Category $category, string $name, string $slug, int $price): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'description' => 'Producto de prueba con descripción completa.',
            'price' => $price,
            'stock' => 10,
            'image' => 'images/products/cafe-tarrazu.svg',
            'featured' => true,
            'is_active' => true,
        ]);
    }
}
