<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receta_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained()->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('ruta_archivo');
            $table->string('tipo_mime');
            $table->unsignedInteger('tamano')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_documentos');
    }
};
