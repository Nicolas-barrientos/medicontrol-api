<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id(); // bigint OK en PostgreSQL

            // Reemplazo de morphs() porque usa unsigned en MySQL
            $table->string('tokenable_type');
            $table->bigInteger('tokenable_id'); // sin unsigned

            $table->index(['tokenable_type', 'tokenable_id']); // índice compuesto

            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
