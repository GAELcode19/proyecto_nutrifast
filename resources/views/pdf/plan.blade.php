@extends('pdf._layout')
@section('title', 'Plan de alimentación')

@section('content')
<table class="grid">
  <tr><td class="label">Paciente</td><td>{{ $paciente->nombre_completo }}</td></tr>
  <tr><td class="label">Indicado en la consulta del</td><td>{{ $consulta ? $consulta->fecha_consulta->format('d/m/Y') : '—' }}</td></tr>
</table>

<h2>Plan de alimentación</h2>
<div class="box">{{ $consulta?->plan_alimentacion ?: 'Sin plan de alimentación registrado.' }}</div>

@if($consulta && $consulta->medicamentos->isNotEmpty())
<h2>Medicamentos y suplementos</h2>
<table class="data">
  <thead><tr><th>Medicamento</th><th>Dosis</th><th>Frecuencia</th></tr></thead>
  <tbody>
    @foreach($consulta->medicamentos as $m)
      <tr><td>{{ $m->nombre }}</td><td>{{ $m->dosis ?: '—' }}</td><td>{{ $m->frecuencia ?: '—' }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

<h2>Próxima cita</h2>
<p>{{ $paciente->proximaCita ? \Illuminate\Support\Str::ucfirst($paciente->proximaCita->fecha_hora->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY, HH:mm [hrs]')) : 'Por agendar.' }}</p>

<div class="firma">{{ $nutriologo }}<br><span class="muted">Nutriólogo(a)</span></div>
@endsection
