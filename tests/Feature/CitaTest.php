<?php

use App\Models\CitaMedica;
use App\Models\MedicoHorario;
use App\Models\MedicoPerfil;
use App\Models\TipoMedico;
use App\Models\User;

beforeEach(function () {
    TipoMedico::factory()->create(['nombre_tipo_medico' => 'Medicina General']);
});

function crearMedicoDisponible(): User
{
    $medico = User::factory()->asMedico()->has(
        MedicoPerfil::factory(), 'medicoPerfil'
    )->create();

    foreach (range(0, 6) as $dia) {
        MedicoHorario::create([
            'medico_id' => $medico->id,
            'dia_semana' => $dia,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '17:00:00',
            'activo' => true,
        ]);
    }

    return $medico;
}

function crearCitaPendiente(): CitaMedica
{
    $medico = crearMedicoDisponible();
    $paciente = User::factory()->asPaciente()->create();

    return CitaMedica::create([
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'fecha_hora' => now()->addDays(2)->setTime(10, 0, 0),
        'motivo' => 'Consulta de prueba',
        'estado' => 'pendiente',
    ]);
}

test('paciente puede ver formulario crear cita', function () {
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->get(route('citas.create'));

    $response->assertStatus(200);
});

test('medico no puede ver formulario crear cita', function () {
    $medico = User::factory()->asMedico()->create();
    $this->actingAs($medico);

    $response = $this->get(route('citas.create'));

    $response->assertStatus(403);
});

test('paciente puede crear una cita', function () {
    $medico = crearMedicoDisponible();
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $fecha = now()->addDays(2)->setTime(10, 0, 0);
    $response = $this->post(route('citas.store'), [
        'medico_id' => $medico->id,
        'fecha_hora' => $fecha->format('Y-m-d H:i:s'),
        'motivo' => 'Dolor de cabeza',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('citas_medicas', [
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'motivo' => 'Dolor de cabeza',
        'estado' => 'pendiente',
    ]);
});

test('crear cita falla con medico inactivo', function () {
    $medico = User::factory()->asMedico()->has(
        MedicoPerfil::factory()->inactive(), 'medicoPerfil'
    )->create();

    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->post(route('citas.store'), [
        'medico_id' => $medico->id,
        'fecha_hora' => now()->addDays(2)->setTime(10, 0, 0)->format('Y-m-d H:i:s'),
        'motivo' => 'Consulta',
    ]);

    $response->assertSessionHas('error');
});

test('crear cita falla con medico no aprobado', function () {
    $medico = User::factory()->asMedico()->has(
        MedicoPerfil::factory()->unapproved(), 'medicoPerfil'
    )->create();

    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->post(route('citas.store'), [
        'medico_id' => $medico->id,
        'fecha_hora' => now()->addDays(2)->setTime(10, 0, 0)->format('Y-m-d H:i:s'),
        'motivo' => 'Consulta',
    ]);

    $response->assertSessionHas('error');
});

test('crear cita falla con fecha pasada', function () {
    $medico = crearMedicoDisponible();
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->post(route('citas.store'), [
        'medico_id' => $medico->id,
        'fecha_hora' => now()->subDay()->format('Y-m-d H:i:s'),
        'motivo' => 'Consulta',
    ]);

    $response->assertSessionHas('error');
});

test('paciente puede ver detalle de su cita', function () {
    $cita = crearCitaPendiente();
    $this->actingAs($cita->paciente);

    $response = $this->get(route('citas.show', $cita->id));

    $response->assertStatus(200);
    $response->assertSee($cita->motivo);
});

test('paciente no puede ver detalle de cita de otro', function () {
    $cita = crearCitaPendiente();
    $otroPaciente = User::factory()->asPaciente()->create();
    $this->actingAs($otroPaciente);

    $response = $this->get(route('citas.show', $cita->id));

    $response->assertStatus(403);
});

test('medico puede ver detalle de su cita', function () {
    $cita = crearCitaPendiente();
    $this->actingAs($cita->medico);

    $response = $this->get(route('citas.show', $cita->id));

    $response->assertStatus(200);
});

test('admin puede ver detalle de cualquier cita', function () {
    $cita = crearCitaPendiente();
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('citas.show', $cita->id));

    $response->assertStatus(200);
});

test('paciente puede cancelar su cita pendiente', function () {
    $cita = crearCitaPendiente();
    $this->actingAs($cita->paciente);

    $response = $this->put(route('citas.estado', $cita->id));

    $response->assertRedirect();
    $this->assertEquals('cancelada', $cita->fresh()->estado);
});

test('paciente no puede cancelar cita confirmada', function () {
    $cita = crearCitaPendiente();
    $cita->update(['estado' => 'confirmada']);
    $this->actingAs($cita->paciente);

    $response = $this->put(route('citas.estado', $cita->id));

    $this->assertEquals('confirmada', $cita->fresh()->estado);
});

test('medico puede confirmar cita pendiente', function () {
    $cita = crearCitaPendiente();
    $this->actingAs($cita->medico);

    $response = $this->put(route('citas.estado', $cita->id), [
        'estado' => 'confirmada',
    ]);

    $response->assertRedirect();
    $this->assertEquals('confirmada', $cita->fresh()->estado);
});

test('medico no puede modificar cita de otro medico', function () {
    $cita = crearCitaPendiente();
    $otroMedico = User::factory()->asMedico()->has(
        MedicoPerfil::factory(), 'medicoPerfil'
    )->create();
    $this->actingAs($otroMedico);

    $response = $this->put(route('citas.estado', $cita->id), [
        'estado' => 'confirmada',
    ]);

    $response->assertStatus(403);
});

test('admin puede modificar estado de cualquier cita', function () {
    $cita = crearCitaPendiente();
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $response = $this->put(route('citas.estado', $cita->id), [
        'estado' => 'confirmada',
    ]);

    $response->assertRedirect();
    $this->assertEquals('confirmada', $cita->fresh()->estado);
});

test('transiciones de estado validas', function (string $from, string $to, array $extra = []) {
    $cita = crearCitaPendiente();
    $cita->update(['estado' => $from]);
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $response = $this->put(route('citas.estado', $cita->id), array_merge(
        ['estado' => $to], $extra
    ));

    $response->assertRedirect();
    $this->assertEquals($to, $cita->fresh()->estado);
})->with([
    ['pendiente', 'confirmada'],
    ['pendiente', 'cancelada'],
    ['pendiente', 'reprogramada', ['fecha_reprogramada' => now()->addWeek()->format('Y-m-d H:i:s')]],
    ['pendiente', 'no_asistio'],
    ['confirmada', 'en_espera'],
    ['confirmada', 'cancelada'],
    ['confirmada', 'reprogramada', ['fecha_reprogramada' => now()->addWeek()->format('Y-m-d H:i:s')]],
    ['confirmada', 'no_asistio'],
    ['en_espera', 'en_consulta'],
    ['en_espera', 'cancelada'],
    ['en_espera', 'no_asistio'],
    ['en_consulta', 'finalizada'],
    ['reprogramada', 'confirmada'],
]);

test('transiciones de estado invalidas', function (string $from, string $to) {
    $cita = crearCitaPendiente();
    $cita->update(['estado' => $from]);
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $response = $this->put(route('citas.estado', $cita->id), [
        'estado' => $to,
    ]);

    $response->assertSessionHas('error');
    $this->assertEquals($from, $cita->fresh()->estado);
})->with([
    ['pendiente', 'en_espera'],
    ['pendiente', 'en_consulta'],
    ['pendiente', 'finalizada'],
    ['confirmada', 'pendiente'],
    ['confirmada', 'en_consulta'],
    ['confirmada', 'finalizada'],
    ['finalizada', 'pendiente'],
    ['cancelada', 'pendiente'],
    ['no_asistio', 'pendiente'],
]);

test('historial se crea al cambiar estado', function () {
    $cita = crearCitaPendiente();
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $this->put(route('citas.estado', $cita->id), [
        'estado' => 'confirmada',
    ]);

    $this->assertDatabaseHas('cita_historiales', [
        'cita_id' => $cita->id,
        'user_id' => $admin->id,
        'estado_anterior' => 'pendiente',
        'estado_nuevo' => 'confirmada',
    ]);
});

test('recepcionista puede modificar estado', function () {
    $cita = crearCitaPendiente();
    $recepcionista = User::factory()->asRecepcionista()->create();
    $this->actingAs($recepcionista);

    $response = $this->put(route('citas.estado', $cita->id), [
        'estado' => 'confirmada',
    ]);

    $response->assertRedirect();
    $this->assertEquals('confirmada', $cita->fresh()->estado);
});
