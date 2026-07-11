<?php

use App\Models\User;
use App\Models\MedicoPerfil;
use App\Models\TipoMedico;

beforeEach(function () {
    TipoMedico::factory()->create(['nombre_tipo_medico' => 'Medicina General']);
});

test('login muestra el formulario', function () {
    $response = $this->get(route('login'));
    $response->assertStatus(200);
    $response->assertSee('Iniciar sesión');
});

test('registro muestra el formulario', function () {
    $response = $this->get(route('register'));
    $response->assertStatus(200);
    $response->assertSee('Registrarse');
});

test('un usuario puede registrarse como paciente', function () {
    $response = $this->post(route('register'), [
        'name' => 'Paciente Test',
        'email' => 'paciente@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'paciente',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'paciente@test.com',
        'role' => 'paciente',
    ]);
});

test('un usuario puede registrarse como medico', function () {
    $response = $this->post(route('register'), [
        'name' => 'Medico Test',
        'email' => 'medico@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'medico',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'medico@test.com',
        'role' => 'medico',
    ]);
    $this->assertDatabaseHas('medico_perfiles', [
        'user_id' => User::where('email', 'medico@test.com')->first()->id,
        'aprobado' => false,
    ]);
});

test('registro requiere password minimo 8 caracteres', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => 'short',
        'password_confirmation' => 'short',
        'role' => 'paciente',
    ]);

    $response->assertSessionHasErrors('password');
});

test('registro requiere rol valido', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'invalid_role',
    ]);

    $response->assertSessionHasErrors('role');
});

test('un usuario puede iniciar sesion', function () {
    $user = User::factory()->asPaciente()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'user@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('login falla con credenciales invalidas', function () {
    $user = User::factory()->asPaciente()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'user@test.com',
        'password' => 'wrong_password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('un usuario puede cerrar sesion', function () {
    $user = User::factory()->asPaciente()->create();

    $this->actingAs($user);
    $response = $this->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('un paciente no puede acceder a rutas de medico', function () {
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->get(route('medico.perfil'));

    $response->assertStatus(403);
});

test('un medico no puede acceder a rutas de paciente', function () {
    $medico = User::factory()->asMedico()->create();
    $this->actingAs($medico);

    $response = $this->get(route('paciente.perfil'));

    $response->assertStatus(403);
});

test('un paciente no puede acceder a rutas de admin', function () {
    $paciente = User::factory()->asPaciente()->create();
    $this->actingAs($paciente);

    $response = $this->get(route('admin.citas'));

    $response->assertStatus(403);
});

test('un admin puede acceder a rutas de admin', function () {
    $admin = User::factory()->asAdmin()->create();
    $this->actingAs($admin);
    TipoMedico::factory()->create(['nombre_tipo_medico' => 'Medicina General']);

    $response = $this->get(route('admin.citas'));

    $response->assertStatus(200);
});

test('invitado es redirigido al login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});
