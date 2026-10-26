<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('pagador_nombre', 150)->nullable();
            $table->dateTime('pago_realizado_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', fn (Blueprint $table) => $table->dropColumn(['pagador_nombre', 'pago_realizado_at']));
    }
};
