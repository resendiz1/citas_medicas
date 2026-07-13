<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@citasmedicas.com'],
            [
                'name'     => 'Administrador',
                'email'    => 'admin@citasmedicas.com',
                'password' => bcrypt('admin123'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('Admin user created: admin@citasmedicas.com / admin123');
    }
}
