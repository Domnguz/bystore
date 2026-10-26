<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'imagen',
        'sku', 'marca', 'modelo', 'especificaciones', 'compatibilidad', 'garantia',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    protected function casts(): array
    {
        return ['precio' => 'decimal:2', 'stock' => 'integer'];
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class)
            ->orderBy('id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
}
