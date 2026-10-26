<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', fn (Blueprint $table) => $table->string('operacion_yape', 150)->nullable()->unique());
        Schema::create('reclamaciones', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('nombre', 150);
            $table->string('documento', 20);
            $table->string('email');
            $table->string('telefono', 20);
            $table->string('direccion');
            $table->boolean('menor')->default(false);
            $table->string('representante')->nullable();
            $table->string('tipo', 20);
            $table->string('bien_tipo', 20);
            $table->text('bien_descripcion');
            $table->decimal('monto', 10, 2)->nullable();
            $table->text('detalle');
            $table->text('solicitud');
            $table->text('respuesta')->nullable();
            $table->string('medio_respuesta')->nullable();
            $table->string('evidencia_respuesta')->nullable();
            $table->dateTime('respondido_at')->nullable();
            $table->unsignedBigInteger('respondido_por')->nullable();
            $table->json('proveedor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Conserva las hojas de reclamación y sus respaldos.');
    }
};
