<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Precios en colones definidos tras comparar presentaciones semejantes
     * del mercado costarricense. La migración actualiza instalaciones existentes.
     */
    public function up(): void
    {
        $this->updateProducts([
            'tarrazu-reserva-340g' => ['name' => 'Tarrazú Reserva 340 g', 'price' => 7900],
            'poas-volcanico-340g' => ['name' => 'Poás Volcánico 340 g', 'price' => 6490],
            'miel-flor-cafe' => ['name' => 'Miel de flor de café', 'price' => 5900],
            'chocolate-oscuro-70' => ['name' => 'Chocolate oscuro 70%', 'price' => 3900],
            'taza-bosque-nuboso' => ['name' => 'Taza Bosque Nuboso', 'price' => 6900],
            'chorreador-tradicional' => ['name' => 'Chorreador tradicional', 'price' => 11900],
            'cold-brew-500ml' => ['name' => 'Café frío artesanal 500 ml', 'price' => 3500],
            'caja-cuatro-origenes' => ['name' => 'Caja cuatro orígenes', 'price' => 15900],
        ]);
    }

    /** Restaura el catálogo original al revertir la migración. */
    public function down(): void
    {
        $this->updateProducts([
            'tarrazu-reserva-340g' => ['name' => 'Tarrazú Reserva 340 g', 'price' => 8500],
            'poas-volcanico-340g' => ['name' => 'Poás Volcánico 340 g', 'price' => 7900],
            'miel-flor-cafe' => ['name' => 'Miel de flor de café', 'price' => 6200],
            'chocolate-oscuro-70' => ['name' => 'Chocolate oscuro 70%', 'price' => 4500],
            'taza-bosque-nuboso' => ['name' => 'Taza Bosque Nuboso', 'price' => 9800],
            'chorreador-tradicional' => ['name' => 'Chorreador tradicional', 'price' => 14500],
            'cold-brew-500ml' => ['name' => 'Cold Brew 500 ml', 'price' => 4200],
            'caja-cuatro-origenes' => ['name' => 'Caja cuatro orígenes', 'price' => 18900],
        ]);
    }

    /** @param array<string, array{name: string, price: int}> $products */
    private function updateProducts(array $products): void
    {
        foreach ($products as $slug => $values) {
            DB::table('products')->where('slug', $slug)->update([
                ...$values,
                'updated_at' => now(),
            ]);
        }
    }
};
