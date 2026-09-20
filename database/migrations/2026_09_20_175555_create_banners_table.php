<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Describe la imagen y sirve como texto alternativo.
            $table->string('titulo');

            // Rutas de los archivos guardados.
            $table->string('imagen');
            $table->string('imagen_movil')->nullable();

            // Primero se muestran los números más pequeños.
            $table->unsignedInteger('orden')->default(0);

            // Un banner desactivado no aparece en la tienda.
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};