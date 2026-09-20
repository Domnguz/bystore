<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    // Tabla que contiene las fotos adicionales de los productos.
    protected $table = 'producto_imagenes';

    // Campos permitidos al guardar una imagen.
    protected $fillable = [
        'producto_id',
        'ruta',
    ];

    // Cada imagen pertenece a un producto.
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}