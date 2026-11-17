@extends('layouts.app')
@section('title', 'Progreso · '.$paciente->nombre_completo.' · NutriFzst')

@section('content')
<main class="nf-content">
  <nav aria-label="Ruta"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('pacientes.index') }}">Pacientes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pacientes.show', $paciente) }}">{{ $paciente->nombre_completo }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Progreso</li>
  </ol></nav>
  <div class="nf-page-head">
    <div><h1>Progreso</h1><div class="nf-sub">{{ $paciente->nombre_completo }}</div></div>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('pacientes.show', $paciente) }}" class="btn btn-outline-secondary">← Volver al expediente</a>
      <a href="{{ route('pacientes.pdf.avance', $paciente) }}" class="btn btn-primary">Descargar avance (PDF)</a>
    </div>
  </div>

  <div class="nf-card nf-card-pad mb-3">
    <div class="row g-3 align-items-end">
      <div class="col-6 col-lg-3"><label for="indicador" class="form-label">Indicador</label><select id="indicador" class="form-select"></select></div>
      <div class="col-6 col-lg-3"><label for="comparar" class="form-label">Comparar con</label><select id="comparar" class="form-select"></select></div>
      <div class="col-6 col-lg-2"><label for="desde" class="form-label">Desde</label><input id="desde" type="date" class="form-control"></div>
      <div class="col-6 col-lg-2"><label for="hasta" class="form-label">Hasta</label><input id="hasta" type="date" class="form-control"></div>
      <div class="col-lg-2"><div class="btn-group w-100" role="group" aria-label="Tipo de vista">
        <button type="button" class="btn btn-primary" data-vista="grafica" aria-pressed="true">Gráfica</button>
        <button type="button" class="btn btn-outline-primary" data-vista="tabla" aria-pressed="false">Tabla</button>
      </div></div>
    </div>
  </div>

  <div id="vista-grafica">
    <div id="progreso-vacio" class="nf-card nf-empty d-none">No hay suficientes mediciones de este indicador en el rango elegido (se necesitan al menos dos).</div>
    <div id="bloque-grafica" class="nf-card nf-card-pad">
      <p id="resumen" class="mb-3"></p>
      <div style="position:relative;height:320px"><canvas id="grafica" role="img"></canvas></div>
    </div>
  </div>
  <div id="vista-tabla" class="d-none">
    <div class="nf-card overflow-hidden"><div class="table-responsive">
      <table class="table nf-table" id="tabla-comparativa"><thead></thead><tbody></tbody></table>
    </div></div>
    <p class="small text-body-secondary mt-2">El renglón resaltado es el indicador seleccionado. “—” indica que no se capturó.</p>
  </div>
</main>
@endsection

@push('scripts')
<script type="application/json" id="nf-progreso-datos">@json($datos)</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/progreso.js') }}"></script>
@endpush
