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
        Schema::create('auditoria_financeira', function (Blueprint $table) {
            $table->id();
            $table->string('lancamento_id')->index();
            $table->string('tipo_lancamento', 20);
            $table->decimal('valor', 14, 2);
            $table->string('tipo_entidade', 50);
            $table->string('entidade_id');
            $table->string('entidade_descricao', 255)->nullable();
            $table->unsignedBigInteger('usuario_logado_id')->nullable();
            $table->string('usuario_logado_nome', 255)->nullable();
            $table->unsignedBigInteger('aprovado_por_id')->nullable();
            $table->string('aprovado_por_nome', 255)->nullable();
            $table->string('descricao_curta', 500);
            $table->json('payload');
            $table->timestamp('criado_em')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_financeira');
    }
};
