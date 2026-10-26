<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoEvento extends Model
{
    protected $fillable = ['pedido_id', 'usuario_id', 'estado', 'nota'];
}
