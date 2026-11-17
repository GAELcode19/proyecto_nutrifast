@extends('pdf._layout')
@section('title', 'Avance del paciente')

@section('content')
@php
  $fmt = fn ($v, $dec = 1) => $v === null ? '—' : number_format($v, $dec);
  $signo = fn ($v, $dec) => $v === null ? '—' : (($v > 0 ? '+' : ($v < 0 ? '−' : '')).number_format(abs($v), $dec));
  $pesos = $consultas->map(fn ($c) => $c->medicion->peso)->filter(fn ($p) => $p !== null);
  $pMin = $pesos->min(); $pMax = $pesos->max();
@endphp

@if($consultas->isEmpty())
  <p class="muted">El paciente aún no tiene mediciones registradas.</p>
@else
  <p>
    {{ $consultas->count() }} {{ $consultas->count() === 1 ? 'medición registrada' : 'mediciones registradas' }},
    del {{ $consultas->first()->fecha_consulta->format('d/m/Y') }} al {{ $consultas->last()->fecha_consulta->format('d/m/Y') }}.
  </p>

  <h2>Resumen por indicador</h2>
  <table class="data">
    <thead><tr><th>Indicador</th><th class="num">Inicial</th><th class="num">Actual</th><th class="num">Cambio</th></tr></thead>
    <tbody>
      @foreach($resumen as $r)
        <tr>
          <td>{{ $r['nombre'] }}{{ $r['unidad'] ? ' ('.$r['unidad'].')' : '' }}</td>
          <td class="num">{{ $fmt($r['inicial'], $r['dec']) }} <span class="muted">{{ $r['fecha_inicial']->format('d/m/y') }}</span></td>
          <td class="num">{{ $fmt($r['actual'], $r['dec']) }} <span class="muted">{{ $r['fecha_actual']->format('d/m/y') }}</span></td>
          <td class="num"><strong>{{ $signo($r['cambio'], $r['dec']) }}</strong></td>
        </tr>
      @endforeach
    </tbody>
  </table>

  @if($pesos->count() > 1)
  <h2>Evolución del peso</h2>
  <table class="data">
    <tbody>
      @foreach($consultas as $c)
        @continue($c->medicion->peso === null)
        @php $ancho = $pMax > $pMin ? 25 + 75 * ($c->medicion->peso - $pMin) / ($pMax - $pMin) : 100; @endphp
        <tr>
          <td style="width:16%">{{ $c->fecha_consulta->format('d/m/Y') }}</td>
          <td><div style="background:#2f7a63;height:10px;width:{{ round($ancho) }}%"></div></td>
          <td class="num" style="width:14%">{{ $fmt($c->medicion->peso) }} kg</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  <h2>Historial de mediciones</h2>
  <table class="data">
    <thead><tr><th>Fecha</th><th class="num">Peso (kg)</th><th class="num">IMC</th><th class="num">% grasa</th><th class="num">% agua</th><th class="num">Masa musc. (kg)</th><th class="num">Grasa visc.</th></tr></thead>
    <tbody>
      @foreach($consultas as $c)
        @php $m = $c->medicion; @endphp
        <tr>
          <td>{{ $c->fecha_consulta->format('d/m/Y') }}</td>
          <td class="num">{{ $fmt($m->peso) }}</td>
          <td class="num">{{ $fmt($m->imc) }}</td>
          <td class="num">{{ $fmt($m->porcentaje_grasa) }}</td>
          <td class="num">{{ $fmt($m->agua_corporal) }}</td>
          <td class="num">{{ $fmt($m->masa_muscular) }}</td>
          <td class="num">{{ $fmt($m->grasa_visceral, 0) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif
@endsection
