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
        Schema::create('registros_tomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('medicamento_id')->constrained('medicamentos')->onDelete('cascade');
            $table->boolean('tomado')->default(false);
            $table->dateTime('fecha_hora');
            $table->text('notas')->nullable();
            $table->timestamps();

            // Índices para mejorar consultas
            $table->index('user_id');
            $table->index('medicamento_id');
            $table->index('fecha_hora');
            $table->index(['user_id', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_tomas');
    }
};