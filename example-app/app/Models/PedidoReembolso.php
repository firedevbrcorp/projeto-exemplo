<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoReembolso extends Model
{
    protected $table = 'pedidos_reembolso';
    protected $fillable = ['empregado_id', 'dataDespesa', 'descricao', 'valor', 'status'];

    public function empregado()
    {
        return $this->belongsTo(Empregado::class);
    }

    public function recibos()
    {
        return $this->hasMany(Recibo::class, 'pedido_id');
    }
}
