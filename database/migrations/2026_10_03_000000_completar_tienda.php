<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['email_verified_at', 'remember_token', 'rol'] as $columna) {
            if (! Schema::hasColumn('users', $columna)) {
                Schema::table('users', function (Blueprint $table) use ($columna) {
                    match ($columna) {
                        'email_verified_at' => $table->timestamp($columna)->nullable(),
                        'remember_token' => $table->rememberToken(),
                        'rol' => $table->string($columna, 20)->default('cliente'),
                    };
                });
            }
        }
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
        if (! Schema::hasColumn('categorias', 'descripcion')) {
            Schema::table('categorias', fn (Blueprint $table) => $table->text('descripcion')->nullable());
        }
        if (Schema::hasColumn('pedidos', 'user_id') && ! Schema::hasColumn('pedidos', 'usuario_id')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->renameColumn('user_id', 'usuario_id'));
        }
        if (! Schema::hasColumn('pedidos', 'fecha')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->dateTime('fecha')->nullable());
        }
        if (! Schema::hasColumn('detalle_pedidos', 'subtotal')) {
            Schema::table('detalle_pedidos', fn (Blueprint $table) => $table->decimal('subtotal', 10, 2)->nullable());
        }
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('clave_checkout', 64)->nullable()->unique();
            $table->string('destinatario', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('metodo_entrega', 20)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('direccion')->nullable();
            $table->string('referencia')->nullable();
            $table->string('tipo_comprobante', 20)->nullable();
            $table->string('documento', 11)->nullable();
            $table->string('razon_social')->nullable();
            $table->decimal('subtotal_productos', 10, 2)->nullable();
            $table->decimal('costo_envio', 10, 2)->nullable();
            $table->boolean('entrega_cotizada')->default(false);
            $table->boolean('stock_reservado')->default(true);
            $table->dateTime('reserva_hasta')->nullable()->index();
            $table->dateTime('pagado_at')->nullable();
            $table->string('referencia_pago', 150)->nullable();
            $table->string('comprobante_pago')->nullable();
            $table->dateTime('comprobante_enviado_at')->nullable();
            $table->string('transportista', 100)->nullable();
            $table->string('seguimiento', 150)->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->index(['usuario_id', 'created_at']);
        });
        Schema::table('detalle_pedidos', fn (Blueprint $table) => $table->string('nombre_producto')->nullable());
        Schema::create('pedido_eventos', function (Blueprint $table) {
            $table->id();
            // Sin FK para compatibilidad con IDs int del esquema original y bigint del nuevo.
            $table->unsignedBigInteger('pedido_id')->index();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('estado');
            $table->string('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Esta migración conserva datos comerciales. Para revertirla, restaura el respaldo previo.');
    }
};
