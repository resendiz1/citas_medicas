<?php

use App\Models\CitaMedica;
use App\Models\MedicoPerfil;
use App\Models\TipoMedico;
use App\Models\User;

beforeEach(function () {
    TipoMedico::factory()->create(['nombre_tipo_medico' => 'Medicina General']);
});

test('admin ve estadisticas en dashboard', function () {
    $admin = User::factory()->asAdmin()->create();
    User::factory()->asPaciente()->count(3)->create();
    User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->count(2)->create();
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('3');
    $response->assertSee('2');
});

test('medico ve sus citas en dashboard', function () {
    $medico = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create();
    $paciente = User::factory()->asPaciente()->create();
    CitaMedica::create([
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'fecha_hora' => now()->addDay(),
        'motivo' => 'Cita de prueba',
        'estado' => 'pendiente',
    ]);
    $this->actingAs($medico);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Cita de prueba');
});

test('medico no ve citas de otros medicos en dashboard', function () {
    $medico1 = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create();
    $medico2 = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create();
    $paciente = User::factory()->asPaciente()->create();
    CitaMedica::create([
        'paciente_id' => $paciente->id,
        'medico_id' => $medico1->id,
        'fecha_hora' => now()->addDay(),
        'motivo' => 'Cita de medico1',
        'estado' => 'pendiente',
    ]);
    $this->actingAs($medico2);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee('Cita de medico1');
});

test('paciente ve sus citas en dashboard', function () {
    $medico = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create();
    $paciente = User::factory()->asPaciente()->create();
    CitaMedica::create([
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'fecha_hora' => now()->addDay(),
        'motivo' => 'Mi cita',
        'estado' => 'pendiente',
    ]);
    $this->actingAs($paciente);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Mi cita');
});

test('paciente ve lista de medicos en dashboard', function () {
    $medico = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create(['name' => 'Dr. Disponible']);
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Dr. Disponible');
});

test('recepcionista ve estadisticas en dashboard', function () {
    $recepcionista = User::factory()->asRecepcionista()->create();
    $this->actingAs($recepcionista);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
});

test('ayuda es accesible para cualquier rol autenticado', function () {
    $user = User::factory()->asPaciente()->create();
    $this->actingAs($user);

    $response = $this->get(route('ayuda'));

    $response->assertStatus(200);
});
