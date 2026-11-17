<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProgresoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/pacientes');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
    Route::get('/pacientes/{paciente}', [PacienteController::class, 'show'])->name('pacientes.show');
    Route::put('/pacientes/{paciente}', [PacienteController::class, 'update'])->name('pacientes.update');

    Route::get('/pacientes/{paciente}/consultas/create', [ConsultaController::class, 'create'])->name('consultas.create');
    Route::post('/pacientes/{paciente}/consultas', [ConsultaController::class, 'store'])->name('consultas.store');
    Route::get('/pacientes/{paciente}/consultas/{consulta}', [ConsultaController::class, 'show'])
        ->scopeBindings()->name('consultas.show');

    Route::get('/pacientes/{paciente}/pdf', [PdfController::class, 'paciente'])->name('pacientes.pdf');
    Route::get('/pacientes/{paciente}/pdf/plan', [PdfController::class, 'plan'])->name('pacientes.pdf.plan');
    Route::get('/pacientes/{paciente}/pdf/avance', [PdfController::class, 'avance'])->name('pacientes.pdf.avance');
    Route::get('/pacientes/{paciente}/consultas/{consulta}/pdf', [PdfController::class, 'consulta'])
        ->scopeBindings()->name('consultas.pdf');

    Route::get('/pacientes/{paciente}/progreso', [ProgresoController::class, 'show'])->name('progreso.show');

    Route::get('/agenda', [CitaController::class, 'index'])->name('citas.index');
});
