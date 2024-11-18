<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('order_date')->useCurrent();
            $table->decimal('tax_rate', 5, 2)->default(18);  // Columna para la tasa de impuestos
            $table->decimal('discount', 10, 2)->default(0);  // Columna para descuentos
            $table->decimal('total_amount', 10, 2)->storedAs('(SELECT (SUM(quantity * unit_price) * (1 + tax_rate / 100) - discount) FROM order_details WHERE order_details.order_id = orders.order_id)');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
