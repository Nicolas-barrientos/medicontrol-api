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
        Schema::create('medicamentos_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('laboratorio_titular')->nullable();
            $table->string('numero_certificado')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('nombre_generico')->nullable();
            $table->string('concentracion')->nullable();
            $table->string('forma_farmaceutica')->nullable();
            $table->string('presentacion')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicamentos_catalogo');
    }
};
