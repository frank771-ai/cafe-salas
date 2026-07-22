<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La cabecera conserva los datos usados al comprar aunque el perfil cambie después.
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('tracking_number')->unique();
            $table->string('status', 30)->default('paid');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);
            $table->string('shipping_address', 500);
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('tax');
            $table->unsignedInteger('shipping');
            $table->unsignedInteger('total');
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->index(['purchased_at', 'status']);
        });

        // Las líneas congelan nombre y precio para mantener facturas históricas correctas.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('line_total');
            $table->timestamps();
        });

        // Solo se persiste una referencia de pasarela y, para tarjeta, cuatro dígitos.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('method', 20);
            $table->string('status', 20)->default('approved');
            $table->string('provider_reference')->unique();
            $table->string('last_four', 4)->nullable();
            $table->unsignedInteger('amount');
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // El orden inverso evita violaciones de integridad referencial.
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
