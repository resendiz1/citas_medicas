<?php

namespace Database\Seeders;

use App\Models\ContactoEmergencia;
use Illuminate\Database\Seeder;

class ContactoEmergenciaSeeder extends Seeder
{
    public function run(): void
    {
        $contactos = [
            ['nombre_completo' => 'María García López', 'telefono' => '555-100-2001', 'email' => 'maria.garcia@email.com'],
            ['nombre_completo' => 'Juan Pérez Hernández', 'telefono' => '555-100-2002', 'email' => 'juan.perez@email.com'],
            ['nombre_completo' => 'Ana Martínez Ruiz', 'telefono' => '555-100-2003', 'email' => 'ana.martinez@email.com'],
            ['nombre_completo' => 'Carlos Sánchez Díaz', 'telefono' => '555-100-2004', 'email' => 'carlos.sanchez@email.com'],
            ['nombre_completo' => 'Laura Ramírez Morales', 'telefono' => '555-100-2005', 'email' => 'laura.ramirez@email.com'],
        ];

        foreach ($contactos as $c) {
            ContactoEmergencia::create($c);
        }
    }
}
