<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La instalación original creó esta tabla manualmente.
        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 10, 2);
                $table->unsignedInteger('stock')->default(0);
                $table->string('imagen')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // No eliminar la tabla preexistente: esta compatibilidad se revierte mediante respaldo.
    }
};
