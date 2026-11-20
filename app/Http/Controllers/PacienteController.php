<?php

namespace App\Http\Controllers;

use App\Http\Requests\PacienteRequest;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\MedicamentoRegistrado;
use App\Models\Medicion;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pacientes = Paciente::query()
            ->with(['ultimaConsulta.medicion', 'proximaCita'])
            ->when($q !== '', function ($query) use ($q) {
                // La colación utf8mb4_unicode_ci ignora acentos y mayúsculas.
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
                $query->whereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", [$like]);
            })
            ->orderBy('apellidos')->orderBy('nombre')
            ->get();

        return view('pacientes.index', [
            'pacientes' => $pacientes,
            'q' => $q,
            'total' => Paciente::count(),
        ]);
    }

    public function store(PacienteRequest $request): RedirectResponse
    {
        $paciente = Paciente::create($request->validated());

        return redirect()->route('pacientes.show', $paciente)->with('status', 'Paciente registrado.');
    }

    public function show(Paciente $paciente)
    {
        $paciente->load(['consultas.medicion', 'consultas.medicamentos', 'proximaCita']);

        $consultas = $paciente->consultas;          // ya vienen en orden cronológico
        $primera = $consultas->first();
        $ultima = $consultas->last();

        $cambio = null;
        if ($consultas->count() > 1 && $primera->medicion && $ultima->medicion) {
            $cambio = round($ultima->medicion->peso - $primera->medicion->peso, 1);
        }

        return view('pacientes.show', [
            'paciente' => $paciente,
            'consultas' => $consultas->reverse()->values(),   // más reciente primero
            'ultima' => $ultima,
            'primera' => $primera,
            'cambio' => $cambio,
        ]);
    }

    public function update(PacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        $paciente->update($request->validated());

        return redirect()->route('pacientes.show', $paciente)->with('status', 'Datos del paciente actualizados.');
    }

    /**
     * Elimina al paciente y todo su historial. Es irreversible, por lo que el servidor exige que se
     * escriba el nombre completo (no basta con un clic). Las llaves foráneas son RESTRICT, así que
     * los registros dependientes se borran explícitamente y en una sola transacción.
     */
    public function destroy(Request $request, Paciente $paciente): RedirectResponse
    {
        $request->validate(['confirmacion' => ['required', 'string']], [], ['confirmacion' => 'confirmación']);

        if (! $this->mismoNombre((string) $request->input('confirmacion'), $paciente->nombre_completo)) {
            return back()->withErrors(
                ['confirmacion' => 'El nombre no coincide. No se eliminó al paciente.'], 'eliminar'
            );
        }

        $nombre = $paciente->nombre_completo;

        DB::transaction(function () use ($paciente) {
            $consultas = Consulta::where('paciente_id', $paciente->id)->pluck('id');
            MedicamentoRegistrado::whereIn('consulta_id', $consultas)->delete();
            Medicion::whereIn('consulta_id', $consultas)->delete();
            Consulta::whereIn('id', $consultas)->delete();
            Cita::where('paciente_id', $paciente->id)->delete();
            $paciente->delete();
        });

        return redirect()->route('pacientes.index')->with('status', "Paciente {$nombre} eliminado.");
    }

    /** Compara ignorando mayúsculas y espacios repetidos. */
    private function mismoNombre(string $a, string $b): bool
    {
        $norm = fn (string $t) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($t)));

        return $norm($a) === $norm($b);
    }
}
