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
            AdminUserSeeder::class,
            MedicoSeeder::class,
            MedicoPerfilSeeder::class,
            PacienteSeeder::class,
            MedicoHorarioSeeder::class,
            MedicoBloqueoSeeder::class,
            CitaMedicaSeeder::class,
            RecetaSeeder::class,
            ConsultaMedicaSeeder::class,
            CitaHistorialSeeder::class,
        ]);
    }
}
