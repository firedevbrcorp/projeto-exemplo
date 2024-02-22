<?php

namespace App\Services;

use App\Models\Empregado;
use App\Models\PedidoReembolso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PedidoReembolsoService
{
    public function create(Empregado $empregado, string $dataDespesa, float $valor, string $descricao): PedidoReembolso
    {
        $limiteData = Carbon::parse($dataDespesa)->addDays(30);
        Log::info("Data limite do emprego para solicitar reembolso: {$limiteData}");
        if (Carbon::now()->greaterThan($limiteData)) {
            Log::error("Data limite ultrapassada: {$limiteData}");
            throw ValidationException::withMessages([
                'dataDespesa' => 'A data da despesa excede o limite de 30 dias para reembolso.'
            ]);
        }
        // Aqui
        Log::info("Valor limite mensal: {$empregado->limiteReembolsoMensal}");
        if ($valor > $empregado->limiteReembolsoMensal) {
            Log::error("Valor solicitado de reembolso ultrapassa o limite configurado", [
                'valor_solicitado' => $valor,
                'valor_limite_empregado' => $empregado->limiteReembolsoMensal
            ]);
            throw ValidationException::withMessages([
                'valor' => 'O valor de reembolso solicitado é maior do que o permitido para o funcionário.'
            ]);
        }

        $pedidoReembolso = new PedidoReembolso();
        // Aqui
        $pedidoReembolso->empregado_id = $empregado->id;
        $pedidoReembolso->fill([
            'valor' => $valor,
            'empregado_id' => $empregado->id,
            'descricao' => $descricao,
            'dataDespesa' => $dataDespesa,
        ]);
        $pedidoReembolso->status = 'Pendente'; // Considerando que o status inicial seja 'Pendente'
        $pedidoReembolso->save();

        Log::info("Solicitação de reembolso persistida com sucesso");
        Log::debug("Solicitação de reembolso persistida com sucesso:", $pedidoReembolso->toArray());

        return $pedidoReembolso;
    }
}
