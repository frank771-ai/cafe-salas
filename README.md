# Origen Tico - Tienda virtual Laravel

Proyecto final de **Tecnologías y Sistemas Web II (ITI-523)**. Es una tienda de café y productos artesanales costarricenses construida con Laravel 12, PHP 8.2, Blade, Bootstrap, JavaScript y MariaDB/MySQL de XAMPP. Incluye un perfil alternativo SQLite para las pruebas automatizadas y para cubrir literalmente la opción indicada en la consigna.

## Funcionalidades

- Registro, inicio/cierre de sesión y perfil editable con historial de pedidos.
- Catálogo por categorías, detalle, imágenes, búsqueda, precio y ordenamiento.
- Carrito en sesión: agregar, actualizar y eliminar; IVA de 13 %, envío y envío gratis.
- Compra con tarjeta o PayPal simulados, factura, confirmación y seguimiento.
- Cookie cifrada con los seis productos vistos recientemente.
- Administración de pedidos, inventario bajo y reportes PDF por mes o cliente.
- Protecciones de Laravel contra CSRF, XSS e inyección SQL; contraseñas con hash y sesiones cifradas.
- 47 pruebas automatizadas con 226 verificaciones en SQLite y MariaDB, incluidas validación Luhn, protección CSRF y regresiones de usabilidad.

## Instalación recomendada: XAMPP y phpMyAdmin

Requisitos: XAMPP con PHP 8.2 o superior, extensiones `pdo_mysql`, `mbstring`, `openssl`, `dom`, `fileinfo`; Composer 2.

1. Abra el panel de XAMPP e inicie **Apache** y **MySQL**.
2. Abra [http://localhost/phpmyadmin](http://localhost/phpmyadmin), seleccione **Nueva** y cree `origen_tico` con cotejamiento `utf8mb4_unicode_ci`.
3. Abra una terminal en la carpeta del proyecto y ejecute:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

4. Visite [http://127.0.0.1:8000](http://127.0.0.1:8000).

Alternativa de phpMyAdmin: en **Importar**, seleccione `database/sql/origen_tico.sql`. Ese respaldo ya contiene esquema y datos demostrativos; no ejecute `migrate --seed` después de importarlo.

### Usuarios de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@origentico.test` | `Admin123!` |
| Cliente | `cliente@origentico.test` | `Cliente123!` |

## Ejecutar con Apache de XAMPP

El directorio público debe ser `public`, nunca la raíz del repositorio. Copie y adapte `deployment/apache-vhost.conf.example` dentro de `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, agregue `127.0.0.1 origentico.test` al archivo `hosts` de Windows y reinicie Apache. Luego use `http://origentico.test`.

## Pruebas y calidad

```powershell
php artisan test
php vendor/bin/pint --test
```

PHPUnit utiliza SQLite en memoria, por lo que no altera `origen_tico`. La auditoría también ejecutó la batería completa contra una base MariaDB temporal de XAMPP. GitHub Actions repite pruebas, formato, caché de rutas/vistas y auditoría de dependencias en cada `push` o `pull request`.

## Publicación en GitHub

Repositorio privado: [github.com/Byroncha1323/origen-tico](https://github.com/Byroncha1323/origen-tico).

La entrega contiene historial Git, workflow de Actions, Dependabot, `.gitignore` y política de seguridad. Para que la docente pueda revisarlo mientras sea privado, agréguela como colaboradora desde **Settings → Collaborators**. Nunca confirme `.env`, contraseñas, `APP_KEY` ni tarjetas reales.

## Documentación

- `docs/MANUAL_TECNICO.md`: instalación, arquitectura, base de datos, uso y seguridad.
- `docs/PRUEBAS.md`: estrategia, casos y comandos de prueba.
- `docs/RUBRICA.md`: evidencia de los 32 criterios evaluativos.
- `docs/GUIA_EXPOSICION.md`: recorrido de demostración y preguntas probables.
- `docs/CODIGO_EXPLICADO.md`: explicación archivo por archivo, flujos y decisiones.
- `docs/REVISION_Y_ATRIBUCION.md`: adaptación, atribución y lista de estudio.
- `docs/AUDITORIA_LANZAMIENTO.md`: pruebas de lanzamiento, riesgos y lista previa a producción.
- `docs/PRUEBAS_USABILIDAD.md`: recorridos de capa 8, hallazgos, correcciones y evidencia de navegador.
- `docs/Documentacion_Origen_Tico.docx`: manual formal para entregar.

## Nota sobre pagos y HTTPS

La pasarela es una simulación académica: valida los datos, aprueba la operación y **nunca almacena** el número completo ni el CVV. Además, los campos de tarjeta no se conservan en la sesión cuando falla una validación. Un cobro real requiere credenciales del comercio y un proveedor como PayPal/Stripe. En producción, la simulación queda bloqueada por defecto, Laravel fuerza HTTPS y `deployment/apache-ssl-vhost.conf.example` muestra la configuración con certificado.
