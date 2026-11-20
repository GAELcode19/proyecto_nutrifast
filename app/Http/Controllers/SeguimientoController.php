<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SeguimientoController extends Controller
{
    public const PERIODOS = [30, 60, 90, 180];

    /** Pacientes sin consulta reciente y sin cita futura, para invitarlos a regresar. */
    public function index(Request $request)
    {
        $dias = (int) $request->query('dias', 30);
        if (! in_array($dias, self::PERIODOS, true)) {
            $dias = 30;
        }

        $limite = now()->subDays($dias);

        $pacientes = Paciente::query()
            ->select('pacientes.*')
            ->selectSub(
                Consulta::query()->selectRaw('MAX(fecha_consulta)')->whereColumn('consultas.paciente_id', 'pacientes.id'),
                'ultima_visita'
            )
            // Con una cita programada vigente ya tienen a qué regresar.
            ->whereDoesntHave('citas', fn ($q) => $q->where('estado', 'programada')->where('fecha_hora', '>=', now()->subHour()))
            ->get()
            ->each(function (Paciente $p) {
                // Sin consultas se cuenta desde que se dio de alta.
                $p->sin_consultas = ! $p->ultima_visita;
                $p->referencia = $p->ultima_visita ? Carbon::parse($p->ultima_visita) : $p->created_at;
                $p->dias_ausente = (int) $p->referencia->copy()->startOfDay()->diffInDays(now()->startOfDay(), true);
            })
            ->filter(fn (Paciente $p) => $p->referencia->lte($limite))
            ->sortByDesc('dias_ausente')
            ->values();

        return view('seguimiento.index', compact('pacientes', 'dias'));
    }
}
