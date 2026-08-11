# Café Salas — Tienda virtual de café y productos costarricenses

Proyecto final de **Tecnologías y Sistemas Web II (ITI-523)**.

- Participantes: Byron Chacón y Franklin Castillo.
- Docente: Ing. Milena Vargas Blanco.
- Exposición: 25 y 26 de agosto de 2026.
- Nombre técnico: `cafe_salas`.
- Identificador para URL o repositorio: `cafe-salas`.

## Funcionalidades

Incluye registro, inicio y cierre de sesión, perfil editable, historial de pedidos, catálogo categorizado, búsqueda y filtros, productos vistos recientemente mediante cookie, carrito, impuestos y envío automáticos, compra simulada con tarjeta o PayPal, factura web/PDF, seguimiento, administración de pedidos e inventario y reportes PDF.

El frontend utiliza Blade, Bootstrap, CSS y JavaScript. El backend utiliza PHP 8.2, Laravel 12 y SQLite.

## Instalación principal con SQLite

Requisitos: PHP 8.2 o superior con `pdo_sqlite` y Composer 2.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite
}
php artisan migrate:fresh --seed
php artisan serve
```

Abra `http://127.0.0.1:8000`. El archivo SQLite local y `.env` están excluidos de Git; una instalación nueva reconstruye todo mediante migraciones y seeders.

### Usuarios de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@cafesalas.test` | `Admin123!` |
| Cliente | `cliente@cafesalas.test` | `Cliente123!` |

Son credenciales académicas. No deben reutilizarse en producción.

## Uso con Apache

El proyecto puede ejecutarse con Apache y SQLite. Consulte `deployment/apache-vhost.conf.example` y apunte siempre el `DocumentRoot` a la carpeta `public`.

### Publicación académica en Vercel

La instalación local mantiene SQLite como base principal. La demostración pública usa Neon PostgreSQL porque las funciones de Vercel no conservan archivos SQLite entre ejecuciones.

Sitio publicado: [cafe-salas.vercel.app](https://cafe-salas.vercel.app).

1. Conecte el repositorio a un proyecto de Vercel y vincule una base Neon.
2. Copie en Vercel las variables de `deployment/env.vercel.example`; Neon proporciona `DATABASE_URL`. Si el cliente PostgreSQL no admite SNI, use la variante documentada en ese archivo sin publicar la contraseña.
3. Genere `APP_KEY` con `php artisan key:generate --show` y guárdela como variable protegida.
4. Defina `DB_CONNECTION=pgsql`, `APP_ENV=production`, `APP_DEBUG=false` y `ALLOW_SIMULATED_PAYMENTS_IN_PRODUCTION=true`.
5. Despliegue. El script `composer run vercel` ejecuta migraciones y carga los datos de demostración de forma repetible.
6. Actualice `APP_URL` con el dominio final de Vercel y vuelva a desplegar.

El punto de entrada está en `api/index.php` y `vercel.json` dirige las solicitudes dinámicas a Laravel sin ocultar los recursos de `public`.

## Verificación

```powershell
php artisan optimize:clear
php artisan route:list
php artisan test
php vendor/bin/pint --test
composer audit
```

Consulte el resultado exacto de la última ejecución en `docs/PRUEBAS.md` y el mapeo de los 32 criterios en `docs/RUBRICA.md`.

## Seguridad

Se aplican validaciones del servidor, CSRF, escape de Blade, consultas parametrizadas con Eloquent, hash de contraseñas, regeneración de sesión, control por rol, autorización por propietario, cabeceras defensivas y transacciones para proteger inventario. La pasarela es simulada: no realiza cargos y no almacena número completo ni CVV.

En producción se debe configurar `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, cookies seguras, correo real, respaldos, monitoreo y una pasarela certificada.

## Documentación

- `docs/Documentacion_Cafe_Salas.docx`: documento técnico final.
- `docs/MANUAL_TECNICO.md`: arquitectura, instalación y diagramas.
- `docs/CODIGO_EXPLICADO.md`: explicación del código por capa.
- `docs/PRUEBAS.md`: pruebas automatizadas, manuales y de usabilidad.
- `docs/GUIA_EXPOSICION.md`: distribución de exposición y preguntas.
- `docs/RUBRICA.md`: matriz de los 32 criterios.

## GitHub

Repositorio: [github.com/frank771-ai/cafe-salas](https://github.com/frank771-ai/cafe-salas). No se confirma `.env`, `APP_KEY`, bases con datos personales, credenciales ni el ZIP destinado a la plataforma universitaria.
