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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('name');
            $table->string('uf', 2);
            $table->unsignedInteger('municipalities')->default(0);
            $table->unsignedBigInteger('population')->default(0);
            $table->string('coordinator');
            $table->unsignedBigInteger('vote_goal')->nullable();
            $table->unsignedBigInteger('votes_projected')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
