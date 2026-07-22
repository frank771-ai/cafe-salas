<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Carga usuarios, catálogo y ventas históricas para demostración y reportes. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // La cuenta administradora se crea de forma explícita porque is_admin no es asignable masivamente.
        User::forceCreate([
            'name' => 'Administración Origen Tico',
            'email' => 'admin@origentico.test',
            'phone' => '8888-0001',
            'address' => 'San José, Costa Rica',
            'is_admin' => true,
            'password' => Hash::make('Admin123!'),
        ]);

        $customer = User::create([
            'name' => 'María Fernanda Solano',
            'email' => 'cliente@origentico.test',
            'phone' => '8888-1122',
            'address' => 'Barrio Escalante, San José, casa 12',
            'password' => 'Cliente123!',
        ]);

        $secondCustomer = User::create([
            'name' => 'Carlos Vargas Mora',
            'email' => 'carlos@origentico.test',
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
        ])->mapWithKeys(fn (array $category) => [$category['slug'] => Category::create($category)]);

        $products = collect([
            ['category' => 'cafe-de-origen', 'name' => 'Tarrazú Reserva 340 g', 'slug' => 'tarrazu-reserva-340g', 'description' => 'Café de altura con notas de cacao, naranja dulce y caramelo. Tueste medio y proceso lavado.', 'price' => 8500, 'stock' => 28, 'image' => 'images/products/cafe-tarrazu.svg', 'featured' => true],
            ['category' => 'cafe-de-origen', 'name' => 'Poás Volcánico 340 g', 'slug' => 'poas-volcanico-340g', 'description' => 'Taza balanceada con aroma floral, acidez de manzana y final de chocolate con leche.', 'price' => 7900, 'stock' => 18, 'image' => 'images/products/cafe-poas.svg', 'featured' => true],
            ['category' => 'dulces-artesanales', 'name' => 'Miel de flor de café', 'slug' => 'miel-flor-cafe', 'description' => 'Miel costarricense de temporada, delicada y floral. Frasco de 300 gramos.', 'price' => 6200, 'stock' => 12, 'image' => 'images/products/miel-cafe.svg', 'featured' => false],
            ['category' => 'dulces-artesanales', 'name' => 'Chocolate oscuro 70%', 'slug' => 'chocolate-oscuro-70', 'description' => 'Chocolate artesanal elaborado con cacao costarricense y un toque de café.', 'price' => 4500, 'stock' => 34, 'image' => 'images/products/chocolate.svg', 'featured' => true],
            ['category' => 'metodos-y-tazas', 'name' => 'Taza Bosque Nuboso', 'slug' => 'taza-bosque-nuboso', 'description' => 'Taza de cerámica de 350 ml pintada a mano por un taller local.', 'price' => 9800, 'stock' => 9, 'image' => 'images/products/taza.svg', 'featured' => true],
            ['category' => 'metodos-y-tazas', 'name' => 'Chorreador tradicional', 'slug' => 'chorreador-tradicional', 'description' => 'Chorreador de madera nacional con bolsa de tela reutilizable y base para taza.', 'price' => 14500, 'stock' => 5, 'image' => 'images/products/chorreador.svg', 'featured' => true],
            ['category' => 'listo-para-disfrutar', 'name' => 'Cold Brew 500 ml', 'slug' => 'cold-brew-500ml', 'description' => 'Café extraído en frío durante 18 horas, suave y naturalmente dulce.', 'price' => 4200, 'stock' => 22, 'image' => 'images/products/cold-brew.svg', 'featured' => false],
            ['category' => 'listo-para-disfrutar', 'name' => 'Caja cuatro orígenes', 'slug' => 'caja-cuatro-origenes', 'description' => 'Degustación de cuatro regiones en presentaciones de 100 g, ideal para regalo.', 'price' => 18900, 'stock' => 14, 'image' => 'images/products/degustacion.svg', 'featured' => true],
        ])->map(function (array $product) use ($categories) {
            $category = $categories[$product['category']];
            unset($product['category']);

            return Product::create(['category_id' => $category->id, ...$product, 'is_active' => true]);
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
        $shipping = $subtotal >= 30000 ? 0 : 2500;
        $total = $subtotal + $tax + $shipping;
        $suffix = strtoupper(substr(hash('sha256', $user->email.$date->timestamp), 0, 6));

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'OT-'.$date->format('Ymd').'-'.$suffix,
            'tracking_number' => 'CRPOST-'.strtoupper(substr(hash('sha256', $suffix), 0, 10)),
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
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $subtotal,
        ]);

        $order->payment()->create([
            'method' => $method,
            'status' => 'approved',
            'provider_reference' => 'SIM-SEED-'.$suffix,
            'last_four' => $method === 'card' ? '1111' : null,
            'amount' => $total,
            'processed_at' => $date,
        ]);
    }
}
