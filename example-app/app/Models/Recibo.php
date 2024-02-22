<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $table = 'recibos';
    protected $fillable = ['pedido_id', 'caminhoArquivo'];

    public function pedidoReembolso()
    {
        return $this->belongsTo(PedidoReembolso::class, 'pedido_id');
    }
}
