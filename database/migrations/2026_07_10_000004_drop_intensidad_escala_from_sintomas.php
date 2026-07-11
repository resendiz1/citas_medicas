<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sintomas', function (Blueprint $table) {
            $table->dropColumn('intensidad_escala');
        });
    }

    public function down(): void
    {
        Schema::table('sintomas', function (Blueprint $table) {
            $table->unsignedTinyInteger('intensidad_escala')->nullable();
        });
    }
};
