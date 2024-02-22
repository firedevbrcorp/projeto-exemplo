<?php

namespace App\Http\Controllers;

use App\Models\Empregado;
use App\Services\PedidoReembolsoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PedidoReembolsoController extends Controller
{
    /**
     * @var PedidoReembolsoService
     */
    protected PedidoReembolsoService $service;

    /**
     * @param PedidoReembolsoService $pedidoReembolsoService
     */
    public function __construct(PedidoReembolsoService $pedidoReembolsoService)
    {
        $this->service = $pedidoReembolsoService;
    }
    /**
     * @OA\Post(
     *     path="/api/pedidos-reembolso",
     *     tags={"Pedidos de Reembolso"},
     *     summary="Cria um novo pedido de reembolso",
     *     description="Cria um novo pedido de reembolso com os dados fornecidos.",
     *     operationId="store",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do pedido de reembolso",
     *         @OA\JsonContent(
     *             required={"empregado_id","dataDespesa","descricao","valor"},
     *             @OA\Property(property="empregado_id", type="integer", format="id", example=1),
     *             @OA\Property(property="dataDespesa", type="string", format="date", example="YYYY-MM-DD"),
     *             @OA\Property(property="descricao", type="string", example="Despesas com viagem"),
     *             @OA\Property(property="valor", type="number", format="float", example=123.45),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido de reembolso criado com sucesso."
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos."
     *     )
     * )
     */
    public function store(Request $request)
    {
        Log::info("Iniciando processo de persistência de um pedido de reembolso", $request->all());
        $validator = Validator::make($request->all(), [
            'empregado_id' => 'required|exists:empregados,id',
            'dataDespesa' => 'required|date|before_or_equal:'.Carbon::now()->toDateString(),
            'descricao' => 'required|string',
            'valor' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error("Dados inválidos para persistência do pedido", $request->all());
            return response()->json($validator->errors(), 400);
        }

        Log::debug("Buscando empregado de id #{$request->empregado_id}");
        $empregado = Empregado::find($request->empregado_id);

        try {
            $pedidoReembolso = $this->service->create($empregado, $request->dataDespesa,
                $request->input('valor'), $request->input('descricao'));
        } catch (ValidationException $exception) {
            return response()->json(['errors' => $exception->errors()], 400);
        }

        return response()->json(['message' => 'Pedido de reembolso criado com sucesso.', 'data' => $pedidoReembolso], 200);
    }
}
