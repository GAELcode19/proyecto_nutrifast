{{-- Modal de alta/edición. $paciente es null cuando es un alta. Los errores llegan en la bolsa "paciente". --}}
@php
  $editando = isset($paciente) && $paciente;
  $err = $errors->getBag('paciente');
  $v = fn ($campo, $defecto = '') => old($campo, $editando ? ($campo === 'fecha_nacimiento' ? $paciente->fecha_nacimiento->format('Y-m-d') : $paciente->{$campo}) : $defecto);
  // Si falló la validación de ESTE formulario, se reabre el modal con los mensajes.
  $reabrir = $err->any() && old('_formulario') === ($editando ? 'editar' : 'alta');
@endphp
<div class="modal fade" id="modal-paciente" tabindex="-1" aria-labelledby="modal-paciente-titulo" aria-hidden="true" @if($reabrir) data-reabrir @endif>
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="POST" novalidate
          action="{{ $editando ? route('pacientes.update', $paciente) : route('pacientes.store') }}">
      @csrf
      @if($editando) @method('PUT') @endif
      <input type="hidden" name="_formulario" value="{{ $editando ? 'editar' : 'alta' }}">
      <div class="modal-header">
        <h2 class="modal-title h5" id="modal-paciente-titulo">{{ $editando ? 'Editar datos del paciente' : 'Nuevo paciente' }}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-5">
            <label for="p-nombre" class="form-label req">Nombre(s)</label>
            <input id="p-nombre" name="nombre" class="form-control {{ $err->has('nombre') ? 'is-invalid' : '' }}" autocomplete="off" maxlength="80" value="{{ $v('nombre') }}">
            @if($err->has('nombre'))<div class="invalid-feedback">{{ $err->first('nombre') }}</div>@endif
          </div>
          <div class="col-md-7">
            <label for="p-apellidos" class="form-label req">Apellidos</label>
            <input id="p-apellidos" name="apellidos" class="form-control {{ $err->has('apellidos') ? 'is-invalid' : '' }}" autocomplete="off" maxlength="100" value="{{ $v('apellidos') }}">
            @if($err->has('apellidos'))<div class="invalid-feedback">{{ $err->first('apellidos') }}</div>@endif
          </div>
          <div class="col-md-4">
            <label for="p-fnac" class="form-label req">Fecha de nacimiento</label>
            <input id="p-fnac" name="fecha_nacimiento" type="date" max="{{ now()->toDateString() }}" class="form-control {{ $err->has('fecha_nacimiento') ? 'is-invalid' : '' }}" value="{{ $v('fecha_nacimiento') }}">
            @if($err->has('fecha_nacimiento'))<div class="invalid-feedback">{{ $err->first('fecha_nacimiento') }}</div>@endif
          </div>
          <div class="col-md-4">
            <label for="p-sexo" class="form-label req">Sexo</label>
            <select id="p-sexo" name="sexo" class="form-select {{ $err->has('sexo') ? 'is-invalid' : '' }}">
              <option value="">Selecciona…</option>
              <option value="F" @selected($v('sexo') === 'F')>Femenino</option>
              <option value="M" @selected($v('sexo') === 'M')>Masculino</option>
            </select>
            @if($err->has('sexo'))<div class="invalid-feedback">{{ $err->first('sexo') }}</div>@endif
          </div>
          <div class="col-md-4">
            <label for="p-est" class="form-label req">Estatura (m)</label>
            <input id="p-est" name="estatura_m" type="number" step="0.01" min="0.5" max="2.5" class="form-control {{ $err->has('estatura_m') ? 'is-invalid' : '' }}" placeholder="1.62" value="{{ $v('estatura_m') }}">
            @if($err->has('estatura_m'))<div class="invalid-feedback">{{ $err->first('estatura_m') }}</div>
            @else<div class="form-text">Se usa para calcular el IMC.</div>@endif
          </div>
          <div class="col-md-6">
            <label for="p-tel" class="form-label req">Teléfono (WhatsApp)</label>
            <input id="p-tel" name="telefono" type="tel" inputmode="tel" autocomplete="off" class="form-control {{ $err->has('telefono') ? 'is-invalid' : '' }}" maxlength="20" placeholder="312 123 4567" value="{{ $v('telefono') }}">
            @if($err->has('telefono'))<div class="invalid-feedback">{{ $err->first('telefono') }}</div>
            @else<div class="form-text">10 dígitos. Si es de otro país, inclúyelo con lada (+1…).</div>@endif
          </div>
          <div class="col-md-6">
            <label for="p-correo" class="form-label">Correo</label>
            <input id="p-correo" name="correo" type="email" class="form-control {{ $err->has('correo') ? 'is-invalid' : '' }}" maxlength="150" value="{{ $v('correo') }}">
            @if($err->has('correo'))<div class="invalid-feedback">{{ $err->first('correo') }}</div>@endif
          </div>
          <div class="col-12">
            <label for="p-ant" class="form-label">Antecedentes</label>
            <textarea id="p-ant" name="antecedentes" class="form-control" rows="3">{{ $v('antecedentes') }}</textarea>
          </div>
        </div>
        <p class="small text-body-secondary mt-3 mb-0"><span class="text-danger">*</span> Campo obligatorio</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Guardar y abrir expediente' }}</button>
      </div>
    </form>
  </div>
</div>
