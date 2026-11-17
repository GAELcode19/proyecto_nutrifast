@extends('layouts.app')
@section('title', $paciente->nombre_completo.' · NutriFzst')

@section('content')
@php $med = $ultima?->medicion; @endphp
<main class="nf-content">
  <nav aria-label="Ruta"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('pacientes.index') }}">Pacientes</a></li><li class="breadcrumb-item active" aria-current="page">{{ $paciente->nombre_completo }}</li></ol></nav>
  <div class="nf-page-head">
    <div>
      <h1>{{ $paciente->nombre_completo }}</h1>
      <div class="nf-sub">{{ $paciente->edad }} años · {{ $paciente->sexo === 'F' ? 'Femenino' : 'Masculino' }} · {{ number_format($paciente->estatura_m, 2) }} m{{ $paciente->telefono ? ' · Tel. '.$paciente->telefono : '' }}</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-outline-secondary" data-abrir-modal>Editar datos</button>
      <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Descargar PDF</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('pacientes.pdf', $paciente) }}">Expediente general</a></li>
          <li><a class="dropdown-item" href="{{ route('pacientes.pdf.plan', $paciente) }}">Plan de alimentación</a></li>
        </ul>
      </div>
      <a class="btn btn-outline-secondary" href="{{ route('pacientes.pdf.avance', $paciente) }}">Descargar avance</a>
      @if($wa = $paciente->whatsappUrl())
        <a class="btn btn-wa" href="{{ $wa }}" target="_blank" rel="noopener" title="Abrir WhatsApp con {{ $paciente->telefono }}">WhatsApp</a>
        @if($paciente->proximaCita)
          <a class="btn btn-wa" href="{{ $paciente->proximaCita->setRelation('paciente', $paciente)->whatsappUrl() }}" target="_blank" rel="noopener">Recordar cita</a>
        @endif
      @else
        <button type="button" class="btn btn-outline-secondary" data-abrir-modal title="Registra un teléfono válido para enviar WhatsApp">Agregar teléfono</button>
      @endif
      @if($consultas->count() >= 2)
        <a class="btn btn-outline-primary" href="{{ route('progreso.show', $paciente) }}">Ver progreso</a>
      @else
        <a class="btn btn-outline-primary disabled" href="#" aria-disabled="true" title="Se necesitan al menos dos consultas">Ver progreso</a>
      @endif
      <a class="btn btn-primary btn-lg" href="{{ route('consultas.create', $paciente) }}">+ Nueva consulta</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="nf-card nf-card-pad h-100"><div class="nf-stat-label">Peso actual</div><div class="nf-stat-value">{{ $med ? number_format($med->peso, 1).' kg' : '—' }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="nf-card nf-card-pad h-100"><div class="nf-stat-label">IMC actual</div><div class="nf-stat-value">{{ $med ? number_format($med->imc, 1) : '—' }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="nf-card nf-card-pad h-100">
      <div class="nf-stat-label">Cambio de peso</div>
      <div class="nf-stat-value {{ $cambio === null ? '' : ($cambio <= 0 ? 'nf-delta-down' : 'nf-delta-up') }}">{{ $cambio === null ? '—' : (($cambio > 0 ? '+' : ($cambio < 0 ? '−' : '')).number_format(abs($cambio), 1).' kg') }}</div>
      @if($cambio !== null)<div class="small text-body-secondary">desde {{ $primera->fecha_consulta->format('d/m/Y') }}</div>@endif
    </div></div>
    <div class="col-6 col-xl-3"><div class="nf-card nf-card-pad h-100"><div class="nf-stat-label">Próxima cita</div><div class="fs-5 fw-semibold">{{ $paciente->proximaCita ? $paciente->proximaCita->fecha_hora->format('d/m/Y H:i') : 'Sin cita' }}</div></div></div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <h2 class="h5 mb-2">Historial de consultas</h2>
      <div class="nf-card overflow-hidden">
        @if($consultas->isEmpty())
          <div class="nf-empty">Este paciente aún no tiene consultas. Usa “Nueva consulta” para registrar la primera.</div>
        @else
          <div class="table-responsive">
            <table class="table nf-table">
              <thead><tr><th>Fecha</th><th>Peso (kg)</th><th>IMC</th><th>Observaciones</th></tr></thead>
              <tbody>
              @foreach($consultas as $c)
                <tr class="nf-row-link" tabindex="0" data-href="{{ route('consultas.show', [$paciente, $c]) }}">
                  <td>{{ $c->fecha_consulta->format('d/m/Y') }}</td>
                  <td class="num">{{ $c->medicion ? number_format($c->medicion->peso, 1) : '—' }}</td>
                  <td class="num">{{ $c->medicion ? number_format($c->medicion->imc, 1) : '—' }}</td>
                  <td class="text-truncate" style="max-width:280px">{!! $c->observaciones ? e($c->observaciones) : '<span class="text-body-secondary">—</span>' !!}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
    <div class="col-lg-4">
      <h2 class="h5 mb-2">Antecedentes</h2>
      <div class="nf-card nf-card-pad mb-3" style="white-space:pre-line">{{ $paciente->antecedentes ?: 'Sin antecedentes registrados.' }}</div>
      <h2 class="h5 mb-2">Plan vigente</h2>
      <div class="nf-card nf-card-pad mb-3" style="white-space:pre-line">{{ $ultima?->plan_alimentacion ?: 'Sin plan registrado.' }}</div>
      <h2 class="h5 mb-2">Medicamentos de la última consulta</h2>
      <ul class="nf-card nf-card-pad list-unstyled mb-0">
        @forelse($ultima?->medicamentos ?? [] as $m)
          <li>{{ $m->nombre }}{{ $m->dosis ? ' · '.$m->dosis : '' }}{{ $m->frecuencia ? ' · '.$m->frecuencia : '' }}</li>
        @empty
          <li class="text-body-secondary">Sin medicamentos registrados.</li>
        @endforelse
      </ul>
    </div>
  </div>
</main>
@include('pacientes._form', ['paciente' => $paciente])
@endsection
