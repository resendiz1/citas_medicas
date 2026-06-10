<?php

namespace App\Http\Controllers;

use App\Models\Alergia;
use App\Models\ContactoEmergencia;
use App\Models\EnfermedadImportante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PacienteController extends Controller
{
    public function perfilShow()
    {
        $user = Auth::user()->load([
            'contactosEmergencia',
            'alergias',
            'enfermedadesImportantes',
            'citasComoPaciente.medico.medicoPerfil.tipoMedico',
        ]);

        $citas = $user->citasComoPaciente()->orderBy('fecha_hora', 'desc')->get();
        $catalogoAlergias = Alergia::orderBy('nombre')->get();
        $catalogoEnfermedades = EnfermedadImportante::orderBy('nombre')->get();

        return view('paciente.perfil', compact('user', 'citas', 'catalogoAlergias', 'catalogoEnfermedades'));
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
        ]);

        if ($request->hasFile('foto')) {
            if ($user->foto_url) {
                Storage::disk('public')->delete($user->foto_url);
            }
            $user->foto_url = $request->file('foto')->store('fotos/' . $user->id, 'public');
            $user->save();
        }

        $user->update($request->only('name', 'email', 'fecha_nacimiento', 'telefono', 'direccion', 'observaciones'));

        return redirect()->route('paciente.perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    public function contactoStore(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono'        => 'required|string|max:50',
            'parentesco'      => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string|max:500',
        ]);

        Auth::user()->contactosEmergencia()->create($request->all());

        return redirect()->route('paciente.perfil')->with('success', 'Contacto agregado.');
    }

    public function contactoUpdate(Request $request, ContactoEmergencia $contacto)
    {
        if ($contacto->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono'        => 'required|string|max:50',
            'parentesco'      => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string|max:500',
        ]);

        $contacto->update($request->all());

        return redirect()->route('paciente.perfil')->with('success', 'Contacto actualizado.');
    }

    public function contactoDestroy(ContactoEmergencia $contacto)
    {
        if ($contacto->user_id !== Auth::id()) {
            abort(403);
        }

        $contacto->delete();

        return redirect()->route('paciente.perfil')->with('success', 'Contacto eliminado.');
    }

    public function alergiaStore(Request $request)
    {
        $request->validate([
            'alergia_id'  => 'required|exists:alergias,id',
            'gravedad'    => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->alergias()->where('alergia_id', $request->alergia_id)->exists()) {
            return redirect()->route('paciente.perfil')->with('error', 'Ya tienes registrada esa alergia.');
        }

        $user->alergias()->attach($request->alergia_id, [
            'gravedad'     => $request->gravedad,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Alergia agregada.');
    }

    public function alergiaUpdate(Request $request, Alergia $alergium)
    {
        $user = Auth::user();

        if (!$user->alergias()->where('alergia_id', $alergium->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'gravedad'     => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $user->alergias()->updateExistingPivot($alergium->id, [
            'gravedad'     => $request->gravedad,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Alergia actualizada.');
    }

    public function alergiaDestroy(Alergia $alergium)
    {
        $user = Auth::user();

        if (!$user->alergias()->where('alergia_id', $alergium->id)->exists()) {
            abort(403);
        }

        $user->alergias()->detach($alergium->id);

        return redirect()->route('paciente.perfil')->with('success', 'Alergia eliminada.');
    }

    public function enfermedadStore(Request $request)
    {
        $request->validate([
            'enfermedad_importante_id' => 'required|exists:enfermedades_importantes,id',
            'fecha_diagnostico'        => 'nullable|date',
            'tratamiento_actual'       => 'nullable|string|max:1000',
            'observaciones'            => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->enfermedadesImportantes()->where('enfermedad_importante_id', $request->enfermedad_importante_id)->exists()) {
            return redirect()->route('paciente.perfil')->with('error', 'Ya tienes registrada esa enfermedad.');
        }

        $user->enfermedadesImportantes()->attach($request->enfermedad_importante_id, [
            'fecha_diagnostico'  => $request->fecha_diagnostico,
            'tratamiento_actual' => $request->tratamiento_actual,
            'observaciones'      => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Enfermedad agregada.');
    }

    public function enfermedadUpdate(Request $request, EnfermedadImportante $enfermedadImportante)
    {
        $user = Auth::user();

        if (!$user->enfermedadesImportantes()->where('enfermedad_importante_id', $enfermedadImportante->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'fecha_diagnostico'  => 'nullable|date',
            'tratamiento_actual' => 'nullable|string|max:1000',
            'observaciones'      => 'nullable|string|max:500',
        ]);

        $user->enfermedadesImportantes()->updateExistingPivot($enfermedadImportante->id, [
            'fecha_diagnostico'  => $request->fecha_diagnostico,
            'tratamiento_actual' => $request->tratamiento_actual,
            'observaciones'      => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Enfermedad actualizada.');
    }

    public function enfermedadDestroy(EnfermedadImportante $enfermedadImportante)
    {
        $user = Auth::user();

        if (!$user->enfermedadesImportantes()->where('enfermedad_importante_id', $enfermedadImportante->id)->exists()) {
            abort(403);
        }

        $user->enfermedadesImportantes()->detach($enfermedadImportante->id);

        return redirect()->route('paciente.perfil')->with('success', 'Enfermedad eliminada.');
    }
}
