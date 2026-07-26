<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verifica recorridos y textos que afectan directamente la experiencia del usuario. */
class UsabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_identity_uses_cafe_salas_brand_and_exact_title(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(
                '<title>Café Salas — Tienda virtual de café y productos costarricenses</title>',
                false
            )
            ->assertSee('Café Salas')
            ->assertDontSee('Origen Tico');
    }

    public function test_guest_returns_to_checkout_after_logging_in_from_the_cart(): void
    {
        $product = $this->product();
        $user = User::factory()->create(['password' => 'Cliente123!']);

        $this->withSession(['cart' => [$product->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('href="'.route('checkout.create').'"', false)
            ->assertDontSee('redirect=', false);

        // Solicitar la compra activa el mecanismo "intended" del middleware auth.
        $this->get(route('checkout.create'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Cliente123!',
        ])->assertRedirect(route('checkout.create'));
    }

    public function test_catalog_accepts_a_maximum_price_without_requiring_a_minimum(): void
    {
        $this->product(price: 8500);
        $this->product(name: 'Caja de regalo', slug: 'caja-regalo', price: 18000);

        $this->get(route('products.index', ['max_price' => 9000]))
            ->assertOk()
            ->assertSee('1 producto')
            ->assertDontSee('Caja de regalo');
    }

    public function test_validation_errors_are_clear_spanish_messages_instead_of_framework_keys(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Prueba UX',
            'email' => 'prueba@example.test',
            'phone' => '12',
            'password' => 'abc',
            'password_confirmation' => 'diferente',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors(['phone', 'password']);

        $messages = $response->getSession()->get('errors')->all();
        $this->assertNotEmpty($messages);

        foreach ($messages as $message) {
            $this->assertStringNotContainsString('validation.', $message);
        }

        $this->assertContains('El formato del campo teléfono no es válido.', $messages);
    }

    public function test_navigation_and_product_controls_expose_accessible_context(): void
    {
        $product = $this->product();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('aria-label="Agregar Café de prueba al carrito"', false);

        $this->withSession(['cart' => [$product->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('aria-label="Carrito con 1 artículo"', false);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('aria-describedby="product-stock"', false);
    }

    public function test_login_does_not_publish_seeded_credentials(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Acceso de demostración')
            ->assertDontSee('admin@cafesalas.test')
            ->assertDontSee('Admin123!');
    }

    private function product(
        string $name = 'Café de prueba',
        string $slug = 'cafe-prueba',
        int $price = 8500,
    ): Product {
        $category = Category::firstOrCreate(
            ['slug' => 'cafe'],
            ['name' => 'Café'],
        );

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'description' => 'Café costarricense preparado para pruebas de usabilidad.',
            'price' => $price,
            'stock' => 10,
            'image' => 'images/products/cafe-tarrazu.svg',
            'featured' => true,
            'is_active' => true,
        ]);
    }
}
