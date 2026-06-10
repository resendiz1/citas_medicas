<?php

namespace App\Http\Controllers;

use App\Models\CitaMedica;
use App\Models\ConsultaMedica;
use App\Models\MedicoPerfil;
use App\Models\RecetaDocumento;
use App\Models\TipoMedico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ========== MÉDICOS ==========

    public function medicos(Request $request)
    {
        $query = User::where('role', 'medico')->with('medicoPerfil.tipoMedico');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $medicos = $query->orderBy('name')->paginate(15);
        return view('admin.medicos.index', compact('medicos'));
    }

    public function medicosShow($id)
    {
        $user = User::where('role', 'medico')->with('medicoPerfil.tipoMedico', 'medicoPerfil.documentos', 'horarios', 'bloqueos')->findOrFail($id);
        $perfil = $user->medicoPerfil;
        $documentos = optional($perfil)->documentos ?? collect();

        return view('admin.medicos.show', compact('user', 'perfil', 'documentos'));
    }

    public function medicosCreate()
    {
        $tiposMedico = TipoMedico::all();
        return view('admin.medicos.create', compact('tiposMedico'));
    }

    public function medicosStore(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|string|min:8',
            'tipo_medico_id'     => 'required|exists:tipo_medicos,id',
            'telefono'           => 'nullable|string|max:20',
            'cedula_profesional' => 'nullable|string|max:50',
            'universidad'        => 'nullable|string|max:255',
            'experiencia_anios'  => 'nullable|integer|min:0',
            'descripcion'        => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'medico',
            'telefono' => $data['telefono'] ?? null,
        ]);

        MedicoPerfil::create([
            'user_id'            => $user->id,
            'tipo_medico_id'     => $data['tipo_medico_id'],
            'cedula_profesional' => $data['cedula_profesional'] ?? null,
            'universidad'        => $data['universidad'] ?? null,
            'experiencia_anios'  => $data['experiencia_anios'] ?? null,
            'descripcion'        => $data['descripcion'] ?? null,
            'activo'             => $request->boolean('activo', true),
        ]);

        return redirect()->route('admin.medicos')->with('success', 'Médico creado correctamente.');
    }

    public function medicosEdit($id)
    {
        $medico = User::where('role', 'medico')->with('medicoPerfil')->findOrFail($id);
        $tiposMedico = TipoMedico::all();
        return view('admin.medicos.edit', compact('medico', 'tiposMedico'));
    }

    public function medicosUpdate(Request $request, $id)
    {
        $medico = User::where('role', 'medico')->findOrFail($id);

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email,' . $id,
            'password'           => 'nullable|string|min:8',
            'tipo_medico_id'     => 'required|exists:tipo_medicos,id',
            'telefono'           => 'nullable|string|max:20',
            'cedula_profesional' => 'nullable|string|max:50',
            'universidad'        => 'nullable|string|max:255',
            'experiencia_anios'  => 'nullable|integer|min:0',
            'descripcion'        => 'nullable|string|max:1000',
        ]);

        $userData = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'telefono' => $data['telefono'] ?? null,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $medico->update($userData);

        $medico->medicoPerfil()->updateOrCreate(
            ['user_id' => $medico->id],
            [
                'tipo_medico_id'     => $data['tipo_medico_id'],
                'cedula_profesional' => $data['cedula_profesional'] ?? null,
                'universidad'        => $data['universidad'] ?? null,
                'experiencia_anios'  => $data['experiencia_anios'] ?? null,
                'descripcion'        => $data['descripcion'] ?? null,
                'activo'             => $request->boolean('activo', true),
            ]
        );

        return redirect()->route('admin.medicos')->with('success', 'Médico actualizado correctamente.');
    }

    public function medicosDestroy($id)
    {
        $medico = User::where('role', 'medico')->findOrFail($id);
        $medico->delete();

        return redirect()->route('admin.medicos')->with('success', 'Médico eliminado correctamente.');
    }

    // ========== CITAS ==========

    public function citas(Request $request)
    {
        $query = CitaMedica::with('paciente', 'medico.medicoPerfil.tipoMedico');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('paciente', fn($p) => $p->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('medico', fn($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $citas = $query->orderBy('fecha_hora')->paginate(20);
        return view('admin.citas.index', compact('citas'));
    }

    public function citasDestroy($id)
    {
        $cita = CitaMedica::with('consultaMedica.dolores', 'recetas.medicamentos', 'recetas.documentos', 'historiales')
            ->findOrFail($id);

        foreach ($cita->recetas as $receta) {
            foreach ($receta->documentos as $doc) {
                if ($doc->ruta_archivo && Storage::disk('public')->exists($doc->ruta_archivo)) {
                    Storage::disk('public')->delete($doc->ruta_archivo);
                }
                $doc->delete();
            }
            $receta->medicamentos()->delete();
            $receta->delete();
        }

        if ($cita->consultaMedica) {
            $cita->consultaMedica->dolores()->delete();
            $cita->consultaMedica()->delete();
        }

        $cita->historiales()->delete();

        $cita->delete();

        return redirect()->route('admin.citas')->with('success', 'Cita eliminada correctamente.');
    }

    // ========== PACIENTES ==========

    public function pacientes(Request $request)
    {
        $query = User::where('role', 'paciente')
            ->with('contactosEmergencia', 'alergias', 'enfermedadesImportantes');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pacientes = $query->orderBy('name')->paginate(15);
        return view('admin.pacientes.index', compact('pacientes'));
    }

    public function pacientesCreate()
    {
        $contactos = \App\Models\ContactoEmergencia::all();
        $alergias = \App\Models\Alergia::all();
        $enfermedades = \App\Models\EnfermedadImportante::all();

        return view('admin.pacientes.create', compact('contactos', 'alergias', 'enfermedades'));
    }

    public function pacientesStore(Request $request)
    {
        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email',
            'password'                    => 'required|string|min:8',
            'fecha_nacimiento'             => 'nullable|date',
            'telefono'                    => 'nullable|string|max:20',
            'direccion'                   => 'nullable|string|max:500',
            'observaciones'               => 'nullable|string|max:1000',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'paciente';

        User::create($data);

        return redirect()->route('admin.pacientes')->with('success', 'Paciente creado correctamente.');
    }

    public function pacientesEdit($id)
    {
        $paciente = User::where('role', 'paciente')->findOrFail($id);
        $contactos = \App\Models\ContactoEmergencia::all();
        $alergias = \App\Models\Alergia::all();
        $enfermedades = \App\Models\EnfermedadImportante::all();

        return view('admin.pacientes.edit', compact('paciente', 'contactos', 'alergias', 'enfermedades'));
    }

    public function pacientesUpdate(Request $request, $id)
    {
        $paciente = User::where('role', 'paciente')->findOrFail($id);

        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email,' . $id,
            'password'                    => 'nullable|string|min:8',
            'fecha_nacimiento'             => 'nullable|date',
            'telefono'                    => 'nullable|string|max:20',
            'direccion'                   => 'nullable|string|max:500',
            'observaciones'               => 'nullable|string|max:1000',
        ]);

        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $paciente->update($data);

        return redirect()->route('admin.pacientes')->with('success', 'Paciente actualizado correctamente.');
    }

    public function pacientesDestroy($id)
    {
        $paciente = User::where('role', 'paciente')->findOrFail($id);
        $paciente->delete();

        return redirect()->route('admin.pacientes')->with('success', 'Paciente eliminado correctamente.');
    }
}
