<?php

use App\Models\MedicoPerfil;
use App\Models\TipoMedico;
use App\Models\User;

beforeEach(function () {
    TipoMedico::factory()->create(['nombre_tipo_medico' => 'Medicina General']);
});

test('admin puede listar medicos', function () {
    $admin = User::factory()->asAdmin()->create();
    $medico = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create(['name' => 'Dr. Juan Perez']);
    $this->actingAs($admin);

    $response = $this->get(route('admin.medicos'));

    $response->assertStatus(200);
    $response->assertSee('Dr. Juan Perez');
});

test('admin puede crear medico', function () {
    $admin = User::factory()->asAdmin()->create();
    $tipo = TipoMedico::first();
    $this->actingAs($admin);

    $response = $this->post(route('admin.medicos.store'), [
        'name' => 'Dr. Test',
        'email' => 'dr@test.com',
        'password' => 'password',
        'tipo_medico_id' => $tipo->id,
        'cedula_profesional' => '1234567890',
        'telefono' => '2381234567',
    ]);

    $response->assertRedirect(route('admin.medicos'));
    $this->assertDatabaseHas('users', ['email' => 'dr@test.com', 'role' => 'medico']);
    $this->assertDatabaseHas('medico_perfiles', [
        'cedula_profesional' => '1234567890',
        'aprobado' => true,
    ]);
});

test('admin puede editar medico', function () {
    $admin = User::factory()->asAdmin()->create();
    $medico = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create();
    $tipo = TipoMedico::first();
    $this->actingAs($admin);

    $response = $this->put(route('admin.medicos.update', $medico->id), [
        'name' => 'Dr. Actualizado',
        'email' => $medico->email,
        'tipo_medico_id' => $tipo->id,
        'cedula_profesional' => '0987654321',
        'telefono' => '2387654321',
    ]);

    $response->assertRedirect(route('admin.medicos'));
    $this->assertDatabaseHas('users', ['id' => $medico->id, 'name' => 'Dr. Actualizado']);
});

test('admin puede eliminar medico', function () {
    $admin = User::factory()->asAdmin()->create();
    $medico = User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.medicos.destroy', $medico->id));

    $response->assertRedirect(route('admin.medicos'));
    $this->assertDatabaseMissing('users', ['id' => $medico->id]);
});

test('admin puede aprobar medico', function () {
    $admin = User::factory()->asAdmin()->create();
    $medico = User::factory()->asMedico()->has(
        MedicoPerfil::factory()->unapproved(), 'medicoPerfil'
    )->create();
    $this->actingAs($admin);

    $response = $this->post(route('admin.medicos.aprobar', $medico->id));

    $response->assertRedirect();
    $this->assertTrue((bool) $medico->fresh()->medicoPerfil->aprobado);
});

test('admin puede listar pacientes', function () {
    $admin = User::factory()->asAdmin()->create();
    User::factory()->asPaciente()->count(3)->create();
    $this->actingAs($admin);

    $response = $this->get(route('admin.pacientes'));

    $response->assertStatus(200);
});

test('admin puede crear paciente', function () {
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('admin.pacientes.store'), [
        'name' => 'Paciente Test',
        'email' => 'pac@test.com',
        'password' => 'password',
        'telefono' => '2381234567',
    ]);

    $response->assertRedirect(route('admin.pacientes'));
    $this->assertDatabaseHas('users', ['email' => 'pac@test.com', 'role' => 'paciente']);
});

test('admin puede editar paciente', function () {
    $admin = User::factory()->asAdmin()->create();
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($admin);

    $response = $this->put(route('admin.pacientes.update', $paciente->id), [
        'name' => 'Paciente Actualizado',
        'email' => $paciente->email,
    ]);

    $response->assertRedirect(route('admin.pacientes'));
    $this->assertDatabaseHas('users', ['id' => $paciente->id, 'name' => 'Paciente Actualizado']);
});

test('admin puede eliminar paciente', function () {
    $admin = User::factory()->asAdmin()->create();
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.pacientes.destroy', $paciente->id));

    $response->assertRedirect(route('admin.pacientes'));
    $this->assertDatabaseMissing('users', ['id' => $paciente->id]);
});

test('admin puede listar citas', function () {
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('admin.citas'));

    $response->assertStatus(200);
});

test('admin puede buscar medicos por nombre', function () {
    $admin = User::factory()->asAdmin()->create();
    User::factory()->asMedico()->has(MedicoPerfil::factory(), 'medicoPerfil')->create(['name' => 'Juan Perez']);
    $this->actingAs($admin);

    $response = $this->get(route('admin.medicos', ['search' => 'Juan']));

    $response->assertStatus(200);
    $response->assertSee('Juan Perez');
});

test('admin puede buscar pacientes por nombre', function () {
    $admin = User::factory()->asAdmin()->create();
    User::factory()->asPaciente()->create(['name' => 'Maria Lopez']);
    $this->actingAs($admin);

    $response = $this->get(route('admin.pacientes', ['search' => 'Maria']));

    $response->assertStatus(200);
    $response->assertSee('Maria Lopez');
});

test('no admin no puede acceder a rutas admin', function () {
    $user = User::factory()->asPaciente()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.medicos'));

    $response->assertStatus(403);
});
