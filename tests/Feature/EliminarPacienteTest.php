<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\MedicamentoRegistrado;
use App\Models\Medicion;
use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EliminarPacienteTest extends TestCase
{
    use RefreshDatabase;

    private function paciente(string $nombre = 'José', string $apellidos = 'Pérez López'): Paciente
    {
        $p = Paciente::create([
            'nombre' => $nombre, 'apellidos' => $apellidos, 'fecha_nacimiento' => '1990-05-10',
            'sexo' => 'M', 'estatura_m' => 1.75, 'telefono' => '5512345678',
        ]);
        $c = $p->consultas()->create(['fecha_consulta' => now()->subDay()]);
        $c->medicion()->create(['peso' => 80, 'imc' => 26.12]);
        $c->medicamentos()->create(['nombre' => 'Omega 3']);
        $p->citas()->create(['fecha_hora' => now()->addDay(), 'estado' => 'programada']);

        return $p;
    }

    private function usuario(): Usuario
    {
        return Usuario::create(['nombre' => 'Ana', 'email' => 'ana@test.mx', 'password' => 'secreto123']);
    }

    public function test_elimina_paciente_con_todo_su_historial(): void
    {
        $p = $this->paciente();
        $otro = $this->paciente('María', 'Soto');

        $this->actingAs($this->usuario())
            ->delete(route('pacientes.destroy', $p), ['confirmacion' => '  josé   pérez lópez '])
            ->assertRedirect(route('pacientes.index'))
            ->assertSessionHas('status');

        $this->assertModelMissing($p);
        $this->assertSame(1, Paciente::count());
        $this->assertSame(1, Consulta::count());
        $this->assertSame(1, Medicion::count());
        $this->assertSame(1, MedicamentoRegistrado::count());
        $this->assertSame(1, Cita::count());
        $this->assertModelExists($otro);
    }

    public function test_no_elimina_si_el_nombre_no_coincide(): void
    {
        $p = $this->paciente();

        $this->actingAs($this->usuario())
            ->delete(route('pacientes.destroy', $p), ['confirmacion' => 'José'])
            ->assertSessionHasErrorsIn('eliminar', 'confirmacion');

        $this->assertModelExists($p);
        $this->assertSame(1, Consulta::count());
    }

    public function test_no_elimina_sin_confirmacion(): void
    {
        $p = $this->paciente();

        $this->actingAs($this->usuario())->delete(route('pacientes.destroy', $p))
            ->assertSessionHasErrors('confirmacion');

        $this->assertModelExists($p);
    }

    public function test_invitado_no_puede_eliminar(): void
    {
        $p = $this->paciente();

        $this->delete(route('pacientes.destroy', $p), ['confirmacion' => $p->nombre_completo])
            ->assertRedirect(route('login'));

        $this->assertModelExists($p);
    }

    public function test_expediente_muestra_zona_de_eliminar_y_no_en_el_listado(): void
    {
        $p = $this->paciente();
        $u = $this->usuario();

        $this->actingAs($u)->get(route('pacientes.show', $p))
            ->assertOk()->assertSee(route('pacientes.destroy', $p), false)->assertSee('Eliminar definitivamente');
        $this->actingAs($u)->get(route('pacientes.index'))
            ->assertOk()->assertDontSee('Eliminar');
    }
}
