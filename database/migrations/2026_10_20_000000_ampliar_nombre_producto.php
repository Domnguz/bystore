<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('nombre', 255)->change();
        });
    }

    public function down(): void
    {
        // No reducir el nombre: productos válidos podrían superar los 150 caracteres.
    }
};
