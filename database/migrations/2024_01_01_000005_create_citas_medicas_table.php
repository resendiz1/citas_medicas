<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('medico_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('fecha_hora');
            $table->text('motivo');
            $table->enum('estado', ['pendiente', 'confirmada', 'en_espera', 'en_consulta', 'finalizada', 'cancelada', 'no_asistio', 'reprogramada'])->default('pendiente');
            $table->text('notas_paciente')->nullable();
            $table->text('notas_medico')->nullable();
            $table->decimal('precio_consulta', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas_medicas');
    }
};
