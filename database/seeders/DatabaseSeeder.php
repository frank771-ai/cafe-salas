<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Carga usuarios, catálogo y ventas históricas para demostración y reportes. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // La cuenta administradora se crea de forma explícita porque is_admin no es asignable masivamente.
        $admin = User::firstOrNew(['email' => 'admin@cafesalas.test']);
        $admin->forceFill([
            'name' => 'Administración Café Salas',
            'phone' => '8888-0001',
            'address' => 'San José, Costa Rica',
            'is_admin' => true,
            'password' => Hash::make('Admin123!'),
        ])->save();

        $customer = User::updateOrCreate(['email' => 'cliente@cafesalas.test'], [
            'name' => 'María Fernanda Solano',
            'phone' => '8888-1122',
            'address' => 'Barrio Escalante, San José, casa 12',
            'password' => 'Cliente123!',
        ]);

        $secondCustomer = User::updateOrCreate(['email' => 'carlos@cafesalas.test'], [
            'name' => 'Carlos Vargas Mora',
            'phone' => '8700-4400',
            'address' => 'San Rafael, Heredia, 200 m norte de la iglesia',
            'password' => 'Cliente123!',
        ]);

        // Se indexan categorías por slug para asociar los productos sin depender de IDs fijos.
        $categories = collect([
            ['name' => 'Café de origen', 'slug' => 'cafe-de-origen', 'description' => 'Granos trazables de regiones cafetaleras de Costa Rica.'],
            ['name' => 'Dulces artesanales', 'slug' => 'dulces-artesanales', 'description' => 'Sabores locales para acompañar cada taza.'],
            ['name' => 'Métodos y tazas', 'slug' => 'metodos-y-tazas', 'description' => 'Accesorios para preparar y servir mejor el café.'],
            ['name' => 'Listo para disfrutar', 'slug' => 'listo-para-disfrutar', 'description' => 'Bebidas y selecciones pensadas para regalar.'],
        ])->mapWithKeys(fn (array $category) => [
            $category['slug'] => Category::updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        $products = collect([
            ['category' => 'cafe-de-origen', 'name' => 'Tarrazú Reserva 340 g', 'slug' => 'tarrazu-reserva-340g', 'description' => 'Café de altura con notas de cacao, naranja dulce y caramelo. Tueste medio y proceso lavado.', 'price' => 7900, 'stock' => 28, 'image' => 'images/products/cafe-tarrazu-real.png', 'featured' => true],
            ['category' => 'cafe-de-origen', 'name' => 'Poás Volcánico 340 g', 'slug' => 'poas-volcanico-340g', 'description' => 'Taza balanceada con aroma floral, acidez de manzana y final de chocolate con leche.', 'price' => 6490, 'stock' => 18, 'image' => 'images/products/cafe-poas-real.png', 'featured' => true],
            ['category' => 'dulces-artesanales', 'name' => 'Miel de flor de café', 'slug' => 'miel-flor-cafe', 'description' => 'Miel costarricense de temporada, delicada y floral. Frasco de 300 gramos.', 'price' => 5900, 'stock' => 12, 'image' => 'images/products/miel-flor-cafe-real.png', 'featured' => false],
            ['category' => 'dulces-artesanales', 'name' => 'Chocolate oscuro 82%', 'slug' => 'chocolate-oscuro-82', 'description' => 'Chocolate artesanal con 82% de cacao costarricense y un toque de café.', 'price' => 3900, 'stock' => 34, 'image' => 'images/products/chocolate-organico-real.png', 'featured' => true],
            ['category' => 'metodos-y-tazas', 'name' => 'Taza Bosque Nuboso', 'slug' => 'taza-bosque-nuboso', 'description' => 'Taza artesanal de madera pintada a mano por un taller local.', 'price' => 6900, 'stock' => 9, 'image' => 'images/products/tazas-madera-real.png', 'featured' => true],
            ['category' => 'metodos-y-tazas', 'name' => 'Chorreador tradicional', 'slug' => 'chorreador-tradicional', 'description' => 'Chorreador de madera nacional con bolsa de tela reutilizable y base para taza.', 'price' => 11900, 'stock' => 5, 'image' => 'images/products/chorreador-real.png', 'featured' => true],
            ['category' => 'listo-para-disfrutar', 'name' => 'Café frío artesanal 500 ml', 'slug' => 'cold-brew-500ml', 'description' => 'Café extraído en frío durante 18 horas, suave y naturalmente dulce.', 'price' => 3500, 'stock' => 22, 'image' => 'images/products/cafe-frio-real.png', 'featured' => false],
            ['category' => 'listo-para-disfrutar', 'name' => 'Caja de las 8 regiones cafetaleras', 'slug' => 'caja-regiones-costa-rica', 'description' => 'Selección de las ocho regiones cafetaleras de Costa Rica en presentaciones de 100 g, ideal para regalo.', 'price' => 15900, 'stock' => 14, 'image' => 'images/products/regiones-cafe-real.png', 'featured' => true],
        ])->map(function (array $product) use ($categories) {
            $category = $categories[$product['category']];
            unset($product['category']);

            return Product::updateOrCreate(
                ['slug' => $product['slug']],
                ['category_id' => $category->id, ...$product, 'is_active' => true],
            );
        });

        // Estas compras permiten demostrar historial y reportes desde la primera ejecución.
        $this->seedOrder($customer, $products[0], 2, now()->subDays(2)->toImmutable(), 'card');
        $this->seedOrder($customer, $products[4], 1, now()->subMonth()->subDays(3)->toImmutable(), 'paypal');
        $this->seedOrder($secondCustomer, $products[7], 1, now()->subDays(8)->toImmutable(), 'card');

    }

    /** Crea un pedido histórico completo con línea y pago asociados. */
    private function seedOrder(User $user, Product $product, int $quantity, CarbonImmutable $date, string $method): void
    {
        $subtotal = $product->price * $quantity;
        $tax = (int) round($subtotal * 0.13);
        $shipping = $subtotal >= CartService::FREE_SHIPPING_FROM ? 0 : CartService::SHIPPING_COST;
        $total = $subtotal + $tax + $shipping;
        $suffix = strtoupper(substr(hash('sha256', $user->email.$product->slug.$method), 0, 6));
        $trackingNumber = 'CRPOST-'.strtoupper(substr(hash('sha256', $suffix), 0, 10));

        $order = Order::updateOrCreate(['tracking_number' => $trackingNumber], [
            'user_id' => $user->id,
            'order_number' => 'CS-'.$date->format('Ymd').'-'.$suffix,
            'status' => $date->isBefore(now()->subWeeks(2)) ? 'delivered' : 'preparing',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'shipping_address' => $user->address,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'purchased_at' => $date,
        ]);

        $order->items()->updateOrCreate(['product_id' => $product->id], [
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $subtotal,
        ]);

        $order->payment()->updateOrCreate([], [
            'method' => $method,
            'status' => 'approved',
            'provider_reference' => 'SIM-SEED-'.$suffix,
            'last_four' => $method === 'card' ? '1111' : null,
            'amount' => $total,
            'processed_at' => $date,
        ]);
    }
}
