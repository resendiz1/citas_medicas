<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dolores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_medica_id')->constrained('consulta_medicas')->cascadeOnDelete();
            $table->string('ubicacion');
            $table->string('intensidad')->nullable();
            $table->string('duracion')->nullable();
            $table->timestamps();
        });

        Schema::table('consulta_medicas', function (Blueprint $table) {
            $table->dropColumn(['dolor_ubicacion', 'dolor_intensidad', 'dolor_duracion']);
        });
    }

    public function down(): void
    {
        Schema::table('consulta_medicas', function (Blueprint $table) {
            $table->string('dolor_ubicacion')->nullable();
            $table->string('dolor_intensidad')->nullable();
            $table->string('dolor_duracion')->nullable();
        });

        Schema::dropIfExists('dolores');
    }
};
