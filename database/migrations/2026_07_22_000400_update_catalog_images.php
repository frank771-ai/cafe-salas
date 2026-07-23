<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Sustituye ilustraciones por fotografías del catálogo. */
    public function up(): void
    {
        $this->updateImages([
            'tarrazu-reserva-340g' => 'images/products/cafe-tarrazu-real.png',
            'poas-volcanico-340g' => 'images/products/cafe-poas-real.png',
            'miel-flor-cafe' => 'images/products/miel-flor-cafe-real.png',
            'chocolate-oscuro-70' => 'images/products/chocolate-organico-real.png',
            'taza-bosque-nuboso' => 'images/products/tazas-madera-real.png',
            'chorreador-tradicional' => 'images/products/chorreador-real.png',
            'cold-brew-500ml' => 'images/products/cafe-frio-real.png',
            'caja-cuatro-origenes' => 'images/products/regiones-cafe-real.png',
        ]);

        DB::table('products')->where('slug', 'taza-bosque-nuboso')->update([
            'description' => 'Taza artesanal de madera pintada a mano por un taller local.',
            'updated_at' => now(),
        ]);
    }

    /** Restaura los recursos originales al revertir. */
    public function down(): void
    {
        $this->updateImages([
            'tarrazu-reserva-340g' => 'images/products/cafe-tarrazu.svg',
            'poas-volcanico-340g' => 'images/products/cafe-poas.svg',
            'miel-flor-cafe' => 'images/products/miel-cafe.svg',
            'chocolate-oscuro-70' => 'images/products/chocolate.svg',
            'taza-bosque-nuboso' => 'images/products/taza.svg',
            'chorreador-tradicional' => 'images/products/chorreador.svg',
            'cold-brew-500ml' => 'images/products/cold-brew.svg',
            'caja-cuatro-origenes' => 'images/products/degustacion.svg',
        ]);

        DB::table('products')->where('slug', 'taza-bosque-nuboso')->update([
            'description' => 'Taza de cerámica de 350 ml pintada a mano por un taller local.',
            'updated_at' => now(),
        ]);
    }

    /** @param array<string, string> $images */
    private function updateImages(array $images): void
    {
        foreach ($images as $slug => $image) {
            DB::table('products')->where('slug', $slug)->update([
                'image' => $image,
                'updated_at' => now(),
            ]);
        }
    }
};
