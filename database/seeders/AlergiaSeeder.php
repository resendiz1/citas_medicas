<?php

namespace Database\Seeders;

use App\Models\Alergia;
use Illuminate\Database\Seeder;

class AlergiaSeeder extends Seeder
{
    public function run(): void
    {
        $alergias = [
            ['nombre' => 'Penicilina', 'descripcion' => 'Alergia a antibióticos tipo penicilina'],
            ['nombre' => 'Polen', 'descripcion' => 'Alergia estacional al polen de plantas'],
            ['nombre' => 'Frutos secos', 'descripcion' => 'Alergia a nueces, almendras, cacahuates, etc.'],
            ['nombre' => 'Lácteos', 'descripcion' => 'Intolerancia o alergia a productos lácteos'],
            ['nombre' => 'Ácaros', 'descripcion' => 'Alergia a ácaros del polvo'],
            ['nombre' => 'Picadura de abeja', 'descripcion' => 'Reacción alérgica a picaduras de abeja'],
            ['nombre' => 'Sulfa', 'descripcion' => 'Alergia a medicamentos con sulfa'],
            ['nombre' => 'Ibuprofeno', 'descripcion' => 'Alergia al ibuprofeno y AINEs'],
        ];

        foreach ($alergias as $a) {
            Alergia::create($a);
        }
    }
}
