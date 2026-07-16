<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    private function authorizeAccess(): void
    {
        if (!auth()->user()->esMedico() && !auth()->user()->esPaciente()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('bug-report.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'categoria' => 'required|in:general,error_visual,funcionalidad,rendimiento,seguridad,otro',
        ]);

        BugReport::create([
            'user_id' => auth()->id(),
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'categoria' => $validated['categoria'],
        ]);

        return redirect()->back()->with('success', 'Reporte enviado correctamente. ¡Gracias por ayudarnos a mejorar!');
    }
}
