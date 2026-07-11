<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sintomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_medica_id')->constrained('consulta_medicas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->string('intensidad_categoria')->nullable();
            $table->unsignedTinyInteger('intensidad_escala')->nullable();
            $table->string('inicio')->nullable();
            $table->string('duracion')->nullable();
            $table->string('frecuencia')->nullable();
            $table->timestamps();
        });

        Schema::table('consulta_medicas', function (Blueprint $table) {
            $table->date('fecha_inicio_sintomas')->nullable()->after('tiempo_evolucion');
            $table->string('forma_inicio')->nullable()->after('fecha_inicio_sintomas');
            $table->string('evolucion')->nullable()->after('forma_inicio');
            $table->text('descripcion_padecimiento')->nullable()->after('evolucion');

            $table->boolean('exploracion_sin_hallazgos')->default(false)->after('exploracion_fisica');
            $table->text('exploracion_estado_general')->nullable()->after('exploracion_sin_hallazgos');
            $table->text('exploracion_cabeza_cuello')->nullable()->after('exploracion_estado_general');
            $table->text('exploracion_respiratorio')->nullable()->after('exploracion_cabeza_cuello');
            $table->text('exploracion_cardiovascular')->nullable()->after('exploracion_respiratorio');
            $table->text('exploracion_abdomen')->nullable()->after('exploracion_cardiovascular');
            $table->text('exploracion_extremidades')->nullable()->after('exploracion_abdomen');
            $table->text('exploracion_neurologico')->nullable()->after('exploracion_extremidades');
            $table->text('exploracion_hallazgos')->nullable()->after('exploracion_neurologico');

            $table->text('diagnostico_diferencial')->nullable()->after('codigo_cie10');
            $table->text('pronostico')->nullable()->after('diagnostico_diferencial');
            $table->text('resumen_clinico')->nullable()->after('pronostico');

            $table->text('plan_medicamentos')->nullable()->after('resumen_clinico');
            $table->text('plan_estudios')->nullable()->after('plan_medicamentos');
            $table->text('plan_procedimientos')->nullable()->after('plan_estudios');
            $table->text('plan_recomendaciones')->nullable()->after('plan_procedimientos');
            $table->text('plan_signos_alarma')->nullable()->after('plan_recomendaciones');
            $table->text('plan_referencia')->nullable()->after('plan_signos_alarma');
            $table->date('plan_seguimiento_fecha')->nullable()->after('plan_referencia');
            $table->text('plan_incapacidad')->nullable()->after('plan_seguimiento_fecha');
        });

        Schema::table('receta_medicamentos', function (Blueprint $table) {
            $table->string('nombre_generico')->nullable()->after('medicamento');
            $table->string('nombre_comercial')->nullable()->after('nombre_generico');
            $table->string('presentacion')->nullable()->after('nombre_comercial');
            $table->string('forma_farmaceutica')->nullable()->after('presentacion');
            $table->string('via_administracion')->nullable()->after('dosis');
            $table->string('cantidad_total')->nullable()->after('duracion');
        });
    }

    public function down(): void
    {
        Schema::table('receta_medicamentos', function (Blueprint $table) {
            $table->dropColumn(['nombre_generico', 'nombre_comercial', 'presentacion', 'forma_farmaceutica', 'via_administracion', 'cantidad_total']);
        });

        Schema::table('consulta_medicas', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_inicio_sintomas', 'forma_inicio', 'evolucion', 'descripcion_padecimiento',
                'exploracion_sin_hallazgos', 'exploracion_estado_general', 'exploracion_cabeza_cuello',
                'exploracion_respiratorio', 'exploracion_cardiovascular', 'exploracion_abdomen',
                'exploracion_extremidades', 'exploracion_neurologico', 'exploracion_hallazgos',
                'diagnostico_diferencial', 'pronostico', 'resumen_clinico',
                'plan_medicamentos', 'plan_estudios', 'plan_procedimientos', 'plan_recomendaciones',
                'plan_signos_alarma', 'plan_referencia', 'plan_seguimiento_fecha', 'plan_incapacidad',
            ]);
        });

        Schema::dropIfExists('sintomas');
    }
};
