<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('base_imponible', 10, 2)->nullable();
            $table->decimal('igv', 10, 2)->nullable();
            $table->decimal('igv_tasa', 5, 2)->default(18);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['base_imponible', 'igv', 'igv_tasa']);
        });
    }
};
