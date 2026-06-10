<?php

namespace Database\Seeders;

use App\Models\EnfermedadImportante;
use Illuminate\Database\Seeder;

class EnfermedadImportanteSeeder extends Seeder
{
    public function run(): void
    {
        $enfermedades = [
            ['nombre' => 'Diabetes tipo 2', 'descripcion' => 'Enfermedad metabólica crónica'],
            ['nombre' => 'Hipertensión arterial', 'descripcion' => 'Presión arterial elevada de forma crónica'],
            ['nombre' => 'Asma', 'descripcion' => 'Enfermedad inflamatoria crónica de las vías respiratorias'],
            ['nombre' => 'Cardiopatía isquémica', 'descripcion' => 'Enfermedad de las arterias coronarias'],
            ['nombre' => 'Artritis reumatoide', 'descripcion' => 'Enfermedad autoinmune que afecta las articulaciones'],
            ['nombre' => 'Hipotiroidismo', 'descripcion' => 'Glándula tiroides poco activa'],
            ['nombre' => 'EPOC', 'descripcion' => 'Enfermedad pulmonar obstructiva crónica'],
            ['nombre' => 'Cáncer', 'descripcion' => 'Antecedente de neoplasia maligna'],
        ];

        foreach ($enfermedades as $e) {
            EnfermedadImportante::create($e);
        }
    }
}
