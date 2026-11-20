# NutriFzst · Cómo volver a ponerlo en línea (Cloudflare Tunnel)

La app corre en esta computadora y Cloudflare le da un link público con HTTPS.
**Solo funciona mientras la PC esté encendida, con internet y con las dos ventanas abiertas.**
El link **cambia cada vez** que se reinicia el túnel.

## Pasos

### 1. Encender MySQL
Abre XAMPP o Laragon y enciende **MySQL** (debe verse en verde).

### 2. Ventana 1 · la app
Abre PowerShell y ejecuta:

```
cd C:\NutriFzst
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Debe decir `Server running on [http://127.0.0.1:8000]`. **No cierres esta ventana.**

Prueba en el navegador: `http://127.0.0.1:8000` (con `127.0.0.1`, no `localhost`). Debe salir el login.

### 3. Ventana 2 · el túnel
Abre **otra** PowerShell y ejecuta:

```
cloudflared tunnel --url http://127.0.0.1:8000
```

Espera unos segundos. En la caja del texto aparece el link nuevo:

```
|  https://palabras-al-azar.trycloudflare.com  |
```

Cópialo. **Ese es el link de hoy.** Puede tardar 10 a 20 segundos en abrir.

### 4. Abrirlo en el celular
Mándate el link por WhatsApp (chat contigo mismo) o por correo.
Para probar que se ve desde fuera, apaga el wifi del celular y usa datos móviles.

### 5. Apagarlo
`Ctrl + C` en las dos ventanas y ciérralas. El link deja de funcionar.

## Si algo falla

| Lo que ves | Qué hacer |
|---|---|
| `Could not open input file artisan` | No estás en la carpeta. Ejecuta `cd C:\NutriFzst` |
| Error de base de datos al iniciar sesión | MySQL está apagado (paso 1) |
| El link muestra error 502 o "Bad gateway" | La ventana 1 (`php artisan serve`) se cerró. Vuelve a abrirla |
| El link no abre | Espera 20 segundos y recarga. Si sigue, `Ctrl + C` en la ventana 2 y repite el paso 3 (saldrá un link nuevo) |
| `ERR Serve tunnel error ... unreachable network` | Normal si después dice `Registered tunnel connection`: se reconectó solo |
| Página sin colores o rota | Revisa que `bootstrap/app.php` conserve `trustProxies` y ejecuta `php artisan config:clear` |
| Error 500 sin detalle | Es normal con `APP_DEBUG=false`. El detalle está en `storage/logs/laravel.log` |
| El login no deja entrar en `http://localhost:8000` | Usa `http://127.0.0.1:8000` (la cookie de sesión es segura) |

## Seguridad (leer antes de compartir el link)

- **Cualquiera que tenga el link ve la pantalla de login.** No lo compartas en grupos ni redes sociales.
- **No uses datos reales de pacientes** hasta crear un usuario de MySQL propio (hoy se usa `root`).
- Para uso real en el consultorio conviene un link fijo con dominio y **Cloudflare Access** (acceso solo por correo autorizado), o un servidor VPS.
- Haz respaldos de la base de datos con regularidad:
  `mysqldump -u USUARIO -p nutrifast > respaldo.sql`

## Configuración que ya está hecha

En `.env` (no se sube a git): `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`,
`SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`.
En `bootstrap/app.php`: `trustProxies` para `127.0.0.1` y `::1`, necesario para que detrás del túnel
los enlaces y estilos salgan como HTTPS.

Si cambias algo del `.env`, ejecuta `php artisan config:clear` antes de volver a levantar la app.
