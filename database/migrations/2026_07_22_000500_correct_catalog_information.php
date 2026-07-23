<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Corrige porcentajes y regiones para que el catálogo coincida con los productos reales. */
    public function up(): void
    {
        DB::table('products')
            ->where('slug', 'chocolate-oscuro-70')
            ->update([
                'name' => 'Chocolate oscuro 82%',
                'slug' => 'chocolate-oscuro-82',
                'description' => 'Chocolate artesanal con 82% de cacao costarricense y un toque de café.',
                'updated_at' => now(),
            ]);

        $regionsProductId = DB::table('products')
            ->where('slug', 'caja-cuatro-origenes')
            ->value('id');

        DB::table('products')
            ->where('slug', 'caja-cuatro-origenes')
            ->update([
                'name' => 'Caja de las 8 regiones cafetaleras',
                'slug' => 'caja-regiones-costa-rica',
                'description' => 'Selección de las ocho regiones cafetaleras de Costa Rica en presentaciones de 100 g, ideal para regalo.',
                'updated_at' => now(),
            ]);

        if ($regionsProductId !== null) {
            // order_items conserva una copia histórica; también se corrige para evitar facturas contradictorias.
            DB::table('order_items')
                ->where('product_id', $regionsProductId)
                ->where('product_name', 'Caja cuatro orígenes')
                ->update([
                    'product_name' => 'Caja de las 8 regiones cafetaleras',
                    'updated_at' => now(),
                ]);
        }
    }

    /** Restaura los textos anteriores si se revierte esta corrección. */
    public function down(): void
    {
        DB::table('products')
            ->where('slug', 'chocolate-oscuro-82')
            ->update([
                'name' => 'Chocolate oscuro 70%',
                'slug' => 'chocolate-oscuro-70',
                'description' => 'Chocolate artesanal elaborado con cacao costarricense y un toque de café.',
                'updated_at' => now(),
            ]);

        $regionsProductId = DB::table('products')
            ->where('slug', 'caja-regiones-costa-rica')
            ->value('id');

        DB::table('products')
            ->where('slug', 'caja-regiones-costa-rica')
            ->update([
                'name' => 'Caja cuatro orígenes',
                'slug' => 'caja-cuatro-origenes',
                'description' => 'Degustación de cuatro regiones en presentaciones de 100 g, ideal para regalo.',
                'updated_at' => now(),
            ]);

        if ($regionsProductId !== null) {
            DB::table('order_items')
                ->where('product_id', $regionsProductId)
                ->where('product_name', 'Caja de las 8 regiones cafetaleras')
                ->update([
                    'product_name' => 'Caja cuatro orígenes',
                    'updated_at' => now(),
                ]);
        }
    }
};
