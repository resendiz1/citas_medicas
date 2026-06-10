<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TipoMedicoSeeder::class,
            AlergiaSeeder::class,
            EnfermedadImportanteSeeder::class,
            ContactoEmergenciaSeeder::class,
            MedicoSeeder::class,
            MedicoPerfilSeeder::class,
            PacienteSeeder::class,
            CitaMedicaSeeder::class,
        ]);

        \App\Models\User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@citas.com',
            'password' => bcrypt('admin123'),
            'role'     => 'admin',
        ]);
    }
}
