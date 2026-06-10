<?php

namespace App\Http\Controllers;

use App\Models\MedicoDocumento;
use App\Models\TipoMedico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicoController extends Controller
{
    public function pacienteShow($id)
    {
        $paciente = User::where('role', 'paciente')
            ->with('contactosEmergencia', 'alergias', 'enfermedadesImportantes')
            ->findOrFail($id);

        $citas = $paciente->citasComoPaciente()
            ->where('medico_id', Auth::id())
            ->orderBy('fecha_hora', 'desc')
            ->get();

        if ($citas->isEmpty()) {
            abort(403, 'No tienes citas con este paciente.');
        }

        return view('medico.paciente-show', compact('paciente', 'citas'));
    }

    public function documentosStore(Request $request)
    {
        $request->validate([
            'documento' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:20480',
            'nombre'    => 'nullable|string|max:255',
        ]);

        $perfil = Auth::user()->medicoPerfil;

        if (!$perfil) {
            return redirect()->back()->with('error', 'No tienes un perfil de médico configurado.');
        }

        $file = $request->file('documento');
        $nombreOriginal = $file->getClientOriginalName();
        $ruta = $file->store('medico-documentos/' . $perfil->id, 'public');

        MedicoDocumento::create([
            'medico_perfil_id' => $perfil->id,
            'nombre'           => $request->input('nombre'),
            'nombre_original'  => $nombreOriginal,
            'ruta_archivo'     => $ruta,
            'tipo_mime'        => $file->getMimeType(),
            'tamano'           => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Documento subido correctamente.');
    }

    public function documentosDestroy($id)
    {
        $doc = MedicoDocumento::findOrFail($id);

        if ($doc->medicoPerfil->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($doc->ruta_archivo);
        $doc->delete();

        return redirect()->back()->with('success', 'Documento eliminado.');
    }

    public function perfilShow()
    {
        $user = Auth::user();
        $perfil = $user->medicoPerfil;
        $tiposMedico = TipoMedico::all();
        $documentos = optional($perfil)->documentos ?? collect();

        return view('medico.perfil', compact('user', 'perfil', 'tiposMedico', 'documentos'));
    }

    public function perfilUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'fecha_nacimiento' => 'nullable|date',
            'telefono'         => 'nullable|string|max:50',
            'direccion'        => 'nullable|string|max:500',
            'observaciones'    => 'nullable|string|max:1000',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'tipo_medico_id'   => 'nullable|exists:tipo_medicos,id',
            'cedula_profesional'  => 'nullable|string|max:100',
            'universidad'      => 'nullable|string|max:255',
            'experiencia_anios' => 'nullable|integer|min:0|max:100',
            'descripcion'      => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('foto')) {
            if ($user->foto_url) {
                Storage::disk('public')->delete($user->foto_url);
            }
            $user->foto_url = $request->file('foto')->store('fotos/' . $user->id, 'public');
            $user->save();
        }

        $user->update($request->only('name', 'email', 'fecha_nacimiento', 'telefono', 'direccion', 'observaciones'));

        $perfil = $user->medicoPerfil;
        if ($perfil) {
            $perfil->update(array_merge(
                $request->only('tipo_medico_id', 'cedula_profesional', 'universidad', 'experiencia_anios', 'descripcion'),
                ['activo' => $request->boolean('activo')]
            ));
        }

        return redirect()->route('medico.perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    public function toggleActivo()
    {
        $perfil = Auth::user()->medicoPerfil;

        if (!$perfil) {
            return redirect()->back()->with('error', 'No tienes un perfil de médico.');
        }

        $perfil->update(['activo' => !$perfil->activo]);

        return redirect()->route('medico.perfil')->with('success', $perfil->activo ? 'Marcado como activo.' : 'Marcado como inactivo.');
    }
}
