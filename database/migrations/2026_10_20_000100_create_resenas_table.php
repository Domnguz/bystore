<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            foreach ([
                'usuario_id' => 'users',
                'producto_id' => 'productos',
                'pedido_id' => 'pedidos',
            ] as $columna => $tablaReferida) {
                $tipoId = Schema::getColumnType($tablaReferida, 'id');
                if (in_array($tipoId, ['int', 'integer'], true)) {
                    $table->integer($columna);
                } else {
                    $table->unsignedBigInteger($columna);
                }
                $table->foreign($columna)->references('id')->on($tablaReferida)->restrictOnDelete();
            }
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->unique(['usuario_id', 'producto_id']);
            $table->index(['producto_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
