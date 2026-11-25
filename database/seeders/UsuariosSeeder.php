<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Cuentas del equipo. Las contraseñas se leen de variables de entorno
 * para no guardarlas en Git. Se puede ejecutar varias veces: no duplica
 * ni cambia las cuentas que ya existen.
 */
class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            ['Oscar Barboza', 'barboza_oscar@gmail.com', 'CLAVE_OSCAR'],
            ['Fabián Tejeda', 'tejedafabian@gmail.com', 'CLAVE_FABIAN'],
            ['Gael Guzmán', 'gael@gmail.com', 'CLAVE_GAEL'],
        ];

        foreach ($cuentas as [$nombre, $email, $variable]) {
            $clave = (string) env($variable, '');

            if ($clave === '') {
                $this->command?->warn("Sin {$variable}: no se crea {$email}");
                continue;
            }

            // El cast "hashed" del modelo encripta la contraseña.
            Usuario::firstOrCreate(['email' => $email], ['nombre' => $nombre, 'password' => $clave]);
        }
    }
}
