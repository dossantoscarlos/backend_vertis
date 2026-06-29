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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('type'); // receita / despesa
            $table->date('transaction_date');
            $table->date('competency_date');
            $table->decimal('projected_cost', 15, 2);
            $table->decimal('final_cost', 15, 2);
            $table->string('entity_type'); // campanha, locais, eventos
            $table->string('entity_external_id')->index(); // referente a: codigo ou id
            $table->string('responsible');
            $table->string('approver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
