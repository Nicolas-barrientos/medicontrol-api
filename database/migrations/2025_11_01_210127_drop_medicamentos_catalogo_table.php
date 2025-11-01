<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('medicamentos_catalogo');
    }

    public function down(): void
    {
        Schema::create('medicamentos_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('principio_activo')->nullable();
            $table->string('laboratorio')->nullable();
            $table->string('presentacion')->nullable();
            $table->timestamps();
        });
    }
};
