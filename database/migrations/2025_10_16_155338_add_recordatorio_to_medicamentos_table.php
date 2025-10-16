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
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->time('hora_recordatorio')->nullable()->after('fin');
        });
    }

    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropColumn('hora_recordatorio');
        });
    }
};
