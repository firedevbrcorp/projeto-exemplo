<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos_reembolso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empregado_id')->constrained('empregados');
            $table->date('dataDespesa');
            $table->string('descricao');
            $table->float('valor');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_reembolso');
    }
};
