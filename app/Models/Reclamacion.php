<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reclamacion extends Model
{
    protected $table = 'reclamaciones';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['proveedor' => 'array', 'menor' => 'boolean', 'respondido_at' => 'datetime', 'monto' => 'decimal:2'];
    }
}
