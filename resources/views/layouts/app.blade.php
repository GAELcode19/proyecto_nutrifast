<!doctype html>
<html lang="es">
<head>
@include('layouts._head')
</head>
<body>
@php
  $icoPacientes = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
  $icoAgenda = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';
  $icoSalir = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>';
  $enAgenda = request()->routeIs('citas.*');
  $enSeg = request()->routeIs('seguimiento.*');
  $icoSeg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>';
@endphp
<form id="form-salir" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>

<div class="nf-shell">
  <aside class="nf-side" aria-label="Menú principal">
    <a class="nf-brand" href="{{ route('pacientes.index') }}"><span class="nf-brand-mark" aria-hidden="true">NF</span>NutriFzst</a>
    <nav class="nf-nav">
      <a href="{{ route('pacientes.index') }}" @class(['active' => ! $enAgenda && ! $enSeg]) @if(! $enAgenda && ! $enSeg) aria-current="page" @endif>{!! $icoPacientes !!}Pacientes</a>
      <a href="{{ route('citas.index') }}" @class(['active' => $enAgenda]) @if($enAgenda) aria-current="page" @endif>{!! $icoAgenda !!}Agenda</a>
      <a href="{{ route('seguimiento.index') }}" @class(['active' => $enSeg]) @if($enSeg) aria-current="page" @endif>{!! $icoSeg !!}Seguimiento</a>
    </nav>
    <div class="nf-side-foot">
      <div class="nf-user">{{ auth()->user()->nombre }}</div>
      <nav class="nf-nav"><button type="submit" form="form-salir">{!! $icoSalir !!}Cerrar sesión</button></nav>
    </div>
  </aside>

  <div class="nf-main">
    <header class="nf-topbar">
      <a class="nf-brand p-0" href="{{ route('pacientes.index') }}"><span class="nf-brand-mark" aria-hidden="true">NF</span><span class="d-none d-sm-inline">NutriFzst</span></a>
      <nav class="d-flex gap-1">
        <a class="btn btn-sm {{ $enAgenda || $enSeg ? 'btn-outline-primary' : 'btn-primary' }}" href="{{ route('pacientes.index') }}">Pacientes</a>
        <a class="btn btn-sm {{ $enAgenda ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('citas.index') }}">Agenda</a>
        <a class="btn btn-sm {{ $enSeg ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('seguimiento.index') }}">Seguimiento</a>
        <button type="submit" form="form-salir" class="btn btn-sm btn-outline-secondary">Salir</button>
      </nav>
    </header>

    @yield('content')
    @yield('savebar')
  </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="nf-toast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true" @if(session('status')) data-aviso="{{ session('status') }}" @endif>
    <div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
