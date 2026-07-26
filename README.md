# Café Salas — Tienda virtual de café y productos costarricenses

Proyecto final de **Tecnologías y Sistemas Web II (ITI-523)**.

- Participantes: Byron Chacón y Franklin Castillo.
- Docente: Ing. Milena Vargas Blanco.
- Exposición: 25 y 26 de agosto de 2026.
- Nombre técnico: `cafe_salas`.
- Identificador para URL o repositorio: `cafe-salas`.

## Funcionalidades

Incluye registro, inicio y cierre de sesión, perfil editable, historial de pedidos, catálogo categorizado, búsqueda y filtros, productos vistos recientemente mediante cookie, carrito, impuestos y envío automáticos, compra simulada con tarjeta o PayPal, factura web/PDF, seguimiento, administración de pedidos e inventario y reportes PDF.

El frontend utiliza Blade, Bootstrap, CSS y JavaScript. El backend utiliza PHP 8.2, Laravel 12 y **SQLite como base principal**. MariaDB/MySQL de XAMPP se conserva únicamente como alternativa.

## Instalación principal con SQLite

Requisitos: PHP 8.2 o superior con `pdo_sqlite`, Composer 2 y Node.js/npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite
}
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Abra `http://127.0.0.1:8000`. El archivo SQLite local y `.env` están excluidos de Git; una instalación nueva reconstruye todo mediante migraciones y seeders.

### Usuarios de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@cafesalas.test` | `Admin123!` |
| Cliente | `cliente@cafesalas.test` | `Cliente123!` |

Son credenciales académicas. No deben reutilizarse en producción.

## Alternativa XAMPP/MariaDB

1. Inicie Apache y MySQL en XAMPP.
2. Cree `cafe_salas` en phpMyAdmin.
3. Copie `.env.mysql.example` como `.env`.
4. Ejecute `php artisan key:generate` y `php artisan migrate:fresh --seed`.

También se entrega `database/sql/cafe_salas.sql` como respaldo importable. SQLite sigue siendo la configuración oficial y predeterminada.

## Verificación

```powershell
php artisan optimize:clear
php artisan route:list
php artisan test
php vendor/bin/pint --test
composer audit
npm run build
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

Repositorio privado real: [github.com/Byroncha1323/cafe-salas](https://github.com/Byroncha1323/cafe-salas). No se confirma `.env`, `APP_KEY`, bases con datos personales, credenciales ni el ZIP destinado a la plataforma universitaria.

## Atribución responsable

Para apoyar el desarrollo, revisión y documentación se utilizó OpenAI Codex. Byron Chacón y Franklin Castillo revisaron, adaptaron, probaron y estudiaron el proyecto, y son responsables de comprender y explicar su funcionamiento.
