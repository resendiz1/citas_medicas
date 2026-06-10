<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_enfermedades_importantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enfermedad_importante_id')->constrained('enfermedades_importantes')->cascadeOnDelete();
            $table->date('fecha_diagnostico')->nullable();
            $table->text('tratamiento_actual')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_enfermedades_importantes');
    }
};
