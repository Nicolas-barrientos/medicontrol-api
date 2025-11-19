<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pami_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->string('droga');
            $table->string('marca')->nullable();
            $table->string('presentacion')->nullable();
            $table->string('laboratorio')->nullable();
            $table->string('cobertura')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pami_medicamentos');
    }
};
