<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('sku', 100)->nullable()->unique();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 150)->nullable();
            $table->text('especificaciones')->nullable();
            $table->text('compatibilidad')->nullable();
            $table->text('garantia')->nullable();
        });
        Schema::table('pedidos', fn (Blueprint $table) => $table->string('comprobante_venta')->nullable());
        Schema::table('detalle_pedidos', fn (Blueprint $table) => $table->string('sku_producto', 100)->nullable());
    }

    public function down(): void
    {
        throw new RuntimeException('Restaura un respaldo para revertir campos con datos comerciales.');
    }
};
