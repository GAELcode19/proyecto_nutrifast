<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Medicion;
use App\Models\Paciente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    /** Expediente del paciente: datos, última medición, plan vigente e historial. */
    public function paciente(Paciente $paciente)
    {
        $paciente->load(['consultas.medicion', 'consultas.medicamentos', 'proximaCita']);

        $pdf = Pdf::loadView('pdf.paciente', [
            'paciente' => $paciente,
            'consultas' => $paciente->consultas->reverse()->values(),
            'ultima' => $paciente->consultas->last(),
        ]);

        return $pdf->download('expediente-'.Str::slug($paciente->nombre_completo).'.pdf');
    }

    /** Plan de alimentación vigente (el de la consulta más reciente que tenga uno) para imprimir y entregar. */
    public function plan(Paciente $paciente)
    {
        $paciente->load(['consultas.medicamentos', 'proximaCita']);

        $consulta = $paciente->consultas->reverse()->first(fn ($c) => filled($c->plan_alimentacion))
            ?? $paciente->consultas->last();

        $pdf = Pdf::loadView('pdf.plan', [
            'paciente' => $paciente,
            'consulta' => $consulta,
            'nutriologo' => auth()->user()->nombre,
        ]);

        return $pdf->download('plan-alimentacion-'.Str::slug($paciente->nombre_completo).'.pdf');
    }

    /** Avance del paciente: cambio de cada indicador entre la primera y la última medición, y su historial. */
    public function avance(Paciente $paciente)
    {
        $consultas = $paciente->consultas()->with('medicion')->get()->filter(fn ($c) => $c->medicion)->values();

        // Por indicador: primer y último valor capturado.
        $resumen = collect(Medicion::INDICADORES)->map(function ($info, $clave) use ($consultas) {
            $conValor = $consultas->filter(fn ($c) => $c->medicion->{$clave} !== null)->values();
            if ($conValor->isEmpty()) {
                return null;
            }
            $inicio = $conValor->first();
            $fin = $conValor->last();

            return [
                'nombre' => $info[0], 'unidad' => $info[1], 'dec' => $info[2],
                'inicial' => $inicio->medicion->{$clave}, 'fecha_inicial' => $inicio->fecha_consulta,
                'actual' => $fin->medicion->{$clave}, 'fecha_actual' => $fin->fecha_consulta,
                'cambio' => $conValor->count() > 1 ? $fin->medicion->{$clave} - $inicio->medicion->{$clave} : null,
            ];
        })->filter();

        $pdf = Pdf::loadView('pdf.avance', [
            'paciente' => $paciente,
            'consultas' => $consultas,
            'resumen' => $resumen,
        ]);

        return $pdf->download('avance-'.Str::slug($paciente->nombre_completo).'.pdf');
    }

    /** Hoja para entregar al paciente con lo indicado en una consulta. */
    public function consulta(Paciente $paciente, Consulta $consulta)
    {
        $consulta->load(['medicion', 'medicamentos']);
        $paciente->load('proximaCita');

        $pdf = Pdf::loadView('pdf.consulta', [
            'paciente' => $paciente,
            'consulta' => $consulta,
            'nutriologo' => auth()->user()->nombre,
        ]);

        return $pdf->download('consulta-'.Str::slug($paciente->nombre_completo).'-'.$consulta->fecha_consulta->format('Y-m-d').'.pdf');
    }
}
