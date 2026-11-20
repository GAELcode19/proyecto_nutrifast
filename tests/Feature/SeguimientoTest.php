<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoTest extends TestCase
{
    use RefreshDatabase;

    private function paciente(string $nombre, ?int $hace = null, bool $conCita = false, string $tel = '5512345678'): Paciente
    {
        $p = Paciente::create([
            'nombre' => $nombre, 'apellidos' => 'Prueba', 'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'F', 'estatura_m' => 1.6, 'telefono' => $tel,
        ]);
        if ($hace !== null) {
            $p->consultas()->create(['fecha_consulta' => now()->subDays($hace)]);
        }
        if ($conCita) {
            $p->citas()->create(['fecha_hora' => now()->addDays(3), 'estado' => 'programada']);
        }

        return $p;
    }

    private function usuario(): Usuario
    {
        return Usuario::create(['nombre' => 'Ana', 'email' => 'ana@test.mx', 'password' => 'secreto123']);
    }

    public function test_lista_solo_pacientes_ausentes_sin_cita(): void
    {
        $this->paciente('Reciente', 5);
        $this->paciente('Ausente', 45);
        $this->paciente('ConCita', 90, true);
        $this->paciente('MuyAusente', 120);

        $html = $this->actingAs($this->usuario())->get(route('seguimiento.index'))
            ->assertOk()->assertSee('Ausente Prueba')->assertSee('https://wa.me/525512345678?text=', false)
            ->assertDontSee('Reciente Prueba')->assertDontSee('ConCita Prueba')->getContent();

        $this->assertLessThan(strpos($html, '>Ausente Prueba'), strpos($html, 'MuyAusente Prueba'), 'El más ausente va primero');
    }

    public function test_el_periodo_filtra_y_un_valor_invalido_usa_30(): void
    {
        $this->paciente('Cuarenta', 45);

        $u = $this->usuario();
        $this->actingAs($u)->get(route('seguimiento.index', ['dias' => 60]))->assertDontSee('Cuarenta Prueba');
        $this->actingAs($u)->get(route('seguimiento.index', ['dias' => 7]))->assertSee('Cuarenta Prueba');
    }

    public function test_paciente_sin_consultas_cuenta_desde_el_alta(): void
    {
        $p = $this->paciente('Nuevo');
        $u = $this->usuario();
        $this->actingAs($u)->get(route('seguimiento.index'))->assertDontSee('Nuevo Prueba');

        $p->forceFill(['created_at' => now()->subDays(40)])->save();
        $this->actingAs($u)->get(route('seguimiento.index'))->assertSee('Nuevo Prueba')->assertSee('Sin consultas');
    }

    public function test_sin_telefono_valido_no_hay_whatsapp(): void
    {
        $this->paciente('SinTel', 50, false, '123');

        $this->actingAs($this->usuario())->get(route('seguimiento.index'))
            ->assertSee('Sin teléfono')->assertDontSee('wa.me', false);
    }

    public function test_requiere_sesion(): void
    {
        $this->get(route('seguimiento.index'))->assertRedirect(route('login'));
    }
}
