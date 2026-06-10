<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_perfil_id')->constrained('medico_perfiles')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('ruta_archivo');
            $table->string('tipo_mime')->nullable();
            $table->unsignedBigInteger('tamano')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_documentos');
    }
};
