<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    // Tabla donde se guardan los banners.
    protected $table = 'banners';

    // Campos que Laravel permite guardar mediante create() y update().
    protected $fillable = [
        'titulo',
        'imagen',
        'imagen_movil',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
}