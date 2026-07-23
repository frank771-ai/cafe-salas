# Código explicado - Origen Tico

Esta guía describe la responsabilidad de cada archivo escrito o adaptado para el proyecto. Su objetivo es que cualquier integrante pueda explicar el sistema durante la exposición, ubicar una regla y modificarla sin adivinar.

## 1. Decisión de base de datos

El enunciado original menciona SQLite. El equipo ejecutará la aplicación con **MariaDB/MySQL de XAMPP y phpMyAdmin**:

- `.env.example` configura `DB_CONNECTION=mysql`, host `127.0.0.1`, puerto `3306`, base `origen_tico` y usuario `root`.
- `database/sql/origen_tico.sql` permite importar el esquema y los datos desde phpMyAdmin.
- Las migraciones usan tipos compatibles con MariaDB y SQLite.
- `.env.sqlite.example` ofrece un perfil alternativo para revisar la compatibilidad literal de la consigna.
- `phpunit.xml` ejecuta las pruebas con SQLite en memoria para que nunca alteren la base de XAMPP.

En otras palabras: **MySQL/MariaDB es la base principal de demostración; SQLite es el motor aislado de pruebas**.

## 2. Flujo MVC

1. El navegador solicita una URL definida en `routes/web.php`.
2. Laravel ejecuta los middleware correspondientes: visitante, autenticado, administrador y encabezados de seguridad.
3. El controlador valida la entrada y coordina modelos o servicios.
4. Eloquent crea consultas parametrizadas para MariaDB/MySQL.
5. El controlador retorna una vista Blade, una redirección o un PDF.
6. Blade escapa `{{ ... }}` automáticamente para prevenir XSS.

## 3. Rutas

### `routes/web.php`

- `/`: portada con categorías, destacados y productos recientes.
- `/productos`: catálogo, búsqueda, filtros, orden y paginación.
- `/productos/{product}`: detalle resuelto por el `slug` del producto.
- `/registro` y `/iniciar-sesion`: disponibles solamente para visitantes.
- `/cerrar-sesion`: POST protegido por autenticación y CSRF.
- `/carrito/*`: consulta, alta, cambio y eliminación del carrito en sesión.
- `/perfil`: datos personales e historial para usuarios autenticados.
- `/comprar`: formulario y confirmación del checkout.
- `/pedidos/{order}/factura`: factura HTML.
- `/pedidos/{order}/factura.pdf`: factura descargable.
- `/administracion/*`: panel, estados y reportes; exige los middleware `auth` y `admin`.

Los nombres de rutas, por ejemplo `products.index`, evitan escribir URLs manuales dentro de las vistas.

## 4. Controladores HTTP

### `HomeController`

- Consulta seis productos destacados.
- Cuenta productos activos por categoría.
- Lee de la cookie solamente IDs de productos.
- Vuelve a consultar la base para no confiar en datos escritos por el navegador.
- Respeta el orden de visitas almacenado en la cookie.

### `ProductController`

`index()`:

- Valida texto, categoría, precios y orden.
- Usa `when()` para aplicar únicamente filtros enviados.
- Usa una lista blanca para ordenar; nunca acepta un nombre de columna arbitrario.
- Pagina nueve productos y conserva la consulta en los enlaces.

`show()`:

- Rechaza productos inactivos con HTTP 404.
- Coloca el producto visitado al inicio de `recent_products`.
- Limita la cookie a seis IDs por treinta días.
- Configura HttpOnly, SameSite Lax y Secure cuando la conexión es HTTPS.
- Consulta otros tres productos de la misma categoría.
- Delega el saneamiento y límite de la cookie a `RecentProductsService`.

### `AuthController`

`register()` recorta los campos, convierte el correo a minúsculas y valida nombre, correo único, teléfono y contraseña confirmada. La contraseña exige ocho caracteres, letras, mayúsculas, minúsculas y números. El cast `hashed` de `User` aplica el hash antes de guardar.

`login()` utiliza `Auth::attempt()` y regenera el ID de sesión para evitar fijación de sesión. La ruta limita los intentos a cinco por minuto.

`logout()` elimina la identidad, invalida la sesión y genera un token CSRF nuevo.

### `ProfileController`

- `show()` pagina los pedidos e incluye líneas y pagos mediante eager loading.
- `update()` permite modificar solo nombre, correo, teléfono y dirección.
- La regla `unique()->ignore()` permite conservar el correo propio, pero rechaza el de otra cuenta.

### `CartController`

Es una capa HTTP delgada. Valida cantidades y delega las reglas reales a `CartService`:

- `index()`: productos y totales.
- `store()`: agrega unidades.
- `update()`: reemplaza cantidad; cero elimina.
- `destroy()`: elimina una línea.

### `CheckoutController`

`create()` impide abrir el checkout con un carrito vacío.

`store()`:

1. Recibe únicamente datos aprobados por `CheckoutRequest`.
2. Inicia una transacción de base de datos.
3. Bloquea los productos con `lockForUpdate()`.
4. Revalida actividad e inventario.
5. Calcula los totales desde precios actuales de la base.
6. Crea la cabecera del pedido y un seguimiento único.
7. Congela producto, precio y cantidad en `order_items`.
8. Descuenta inventario.
9. Solicita la autorización simulada a `SimulatedPaymentGateway`.
10. Actualiza teléfono y dirección del perfil.
11. Confirma la transacción y vacía el carrito.

Si cualquier paso falla, Laravel revierte toda la transacción.

`confirmation()` comprueba que el usuario sea dueño del pedido o administrador.

### `InvoiceController`

- Usa una comprobación común de propietario.
- `show()` entrega la factura web.
- `pdf()` genera el archivo con `PdfService`.

### `AdminController`

- Calcula ventas y pedidos del mes.
- Cuenta clientes no administradores.
- alerta sobre productos con cinco unidades o menos.
- Pagina pedidos recientes.
- Solo permite las transiciones declaradas en `Order::STATUS_TRANSITIONS`.
- Al cancelar, `OrderStatusService` marca el pago como reembolsado y devuelve el inventario una sola vez.

### `ReportController`

- `monthly()` valida `YYYY-MM`, calcula inicio y fin del mes y excluye cancelaciones.
- `customer()` valida que el usuario exista y obtiene sus ventas no canceladas.
- Ambos métodos incluyen pedido, cliente, líneas y pago y descargan un PDF horizontal.

## 5. Validación y middleware

### `CheckoutRequest`

- Exige autenticación.
- Normaliza identidad, teléfono, dirección, PayPal y número de tarjeta.
- Valida datos de entrega.
- Aplica campos condicionales para tarjeta o PayPal.
- Comprueba el formato y vigencia de `MM/AA`.
- aplica el algoritmo de Luhn al número de tarjeta.
- valida CVV de tres o cuatro dígitos.
- nunca guarda tarjeta completa ni CVV y `bootstrap/app.php` impide conservarlos como entrada anterior.

### `AdminMiddleware`

Comprueba `is_admin`. Si el usuario no tiene el rol, responde HTTP 403 antes de ejecutar el controlador.

### `SecurityHeaders`

Añade:

- `X-Content-Type-Options: nosniff`.
- `X-Frame-Options: SAMEORIGIN`.
- política estricta de referencia.
- bloqueo de cámara, micrófono y geolocalización.
- Content Security Policy para scripts, estilos, imágenes, formularios y marcos.
- `Cache-Control: no-store` en páginas autenticadas.
- HSTS únicamente cuando la solicitud ya usa HTTPS.

## 6. Servicios

### `CartService`

El carrito se guarda como un mapa `producto_id => cantidad` en la sesión. Sus constantes son:

- `TAX_RATE = 0.13`.
- `SHIPPING_COST = 2500`.
- `FREE_SHIPPING_FROM = 20000`.

`items()` reconsulta los productos, agrega la relación de categoría y calcula cada línea. `totals()` devuelve subtotal, impuesto, envío y total. Todos los importes son enteros de colones; así se evitan errores de punto flotante.

### `SimulatedPaymentGateway`

Representa la respuesta de un procesador académico. Devuelve método, estado aprobado, referencia aleatoria, monto, fecha y últimos cuatro dígitos. El controlador no conoce cómo se produce esa autorización, por lo que en el futuro la clase puede sustituirse por un SDK real.

No se realizan cargos reales porque faltan las credenciales comerciales, webhooks y acuerdos de un proveedor de pagos. En `APP_ENV=production` la simulación se rechaza por defecto para evitar una venta ficticia.

### `RecentProductsService`

Decodifica la cookie sin confiar en ella, acepta únicamente IDs positivos, elimina duplicados y conserva como máximo seis. Así una cookie corrupta o manipulada no provoca errores ni consultas ilimitadas.

### `OrderStatusService`

Bloquea el pedido en una transacción, aplica el flujo pagado → preparación → enviado → entregado y permite cancelar únicamente antes del envío. La cancelación es idempotente: repone stock y cambia el pago a `refunded` una sola vez.

### `PdfService`

- Renderiza una vista Blade con Dompdf.
- usa DejaVu Sans para soportar caracteres españoles y el símbolo de colón.
- deshabilita recursos remotos.
- entrega `Content-Type: application/pdf` y `nosniff`.

## 7. Modelos Eloquent

### `User`

- Campos editables: nombre, correo, teléfono, dirección y contraseña.
- Campos ocultos: contraseña y token de recordatorio.
- Casts: fecha verificada, contraseña con hash y rol booleano.
- Relación `hasMany` con pedidos.

### `Category`

- Contiene nombre, slug y descripción.
- Relación `hasMany` con productos.
- Usa slug para route model binding.

### `Product`

- Pertenece a una categoría.
- Precio y stock son enteros.
- `scopeActive()` centraliza el filtro de productos visibles.
- usa slug en URLs.

### `Order`

- Conserva identidad, dirección, fecha, seguimiento y cuatro totales.
- pertenece a un usuario.
- tiene muchas líneas y un pago.
- usa `order_number` en la URL.
- define la lista de estados administrativos.

### `OrderItem`

Congela nombre, precio, cantidad y total de línea. La relación con producto es nullable para que una factura histórica siga siendo válida aunque un producto se elimine.

### `Payment`

Guarda método, estado, referencia, últimos cuatro dígitos, monto y fecha. Pertenece a un solo pedido.

## 8. Migraciones y tablas

### `users`

Identidad, perfil, rol, contraseña con hash y marcas de tiempo.

### `sessions`

ID de sesión, usuario, IP, agente, contenido cifrado y última actividad.

### `categories`

Nombre, slug único y descripción.

### `products`

Categoría, nombre, slug, descripción, precio, inventario, imagen, destacado y activo. Tiene índices para categoría, actividad, nombre y precio.

### `orders`

Usuario, números únicos de pedido y seguimiento, estado, copia de datos del cliente, subtotal, impuesto, envío, total y fecha.

### `order_items`

Pedido, producto opcional, copia del nombre, precio unitario, cantidad y total.

### `payments`

Un pago por pedido, método, estado, referencia única, últimos cuatro dígitos, monto y fecha.

Las migraciones `down()` eliminan primero las tablas hijas para mantener la integridad referencial.

## 9. Datos de demostración

`DatabaseSeeder` crea:

- un administrador;
- dos clientes;
- cuatro categorías;
- ocho productos;
- tres pedidos históricos con pago y seguimiento.

Los pedidos históricos permiten demostrar el perfil y los reportes inmediatamente después de `migrate --seed`.

## 10. Vistas Blade

- `layouts/app.blade.php`: plantilla, navegación, mensajes, errores, pie y scripts.
- `components/product-card.blade.php`: tarjeta reutilizable con formulario para agregar.
- `home.blade.php`: portada, categorías, destacados y productos de la cookie.
- `products/index.blade.php`: filtros GET, resultados y paginación.
- `products/show.blade.php`: detalle, inventario, cantidad y relacionados.
- `auth/login.blade.php`: acceso y cuentas demostrativas.
- `auth/register.blade.php`: creación de cuenta y reglas visibles.
- `cart/index.blade.php`: líneas, cambios, eliminación y resumen.
- `checkout/create.blade.php`: entrega, métodos de pago y total.
- `checkout/confirmation.blade.php`: pedido, seguimiento, método y factura.
- `profile/show.blade.php`: edición e historial paginado.
- `orders/invoice.blade.php`: factura web.
- `admin/dashboard.blade.php`: indicadores y estados.
- `admin/reports.blade.php`: parámetros de reportes PDF.
- `pdf/invoice.blade.php`: factura autocontenida para Dompdf.
- `pdf/sales-report.blade.php`: tabla horizontal de ventas.

Los formularios que modifican estado usan `@csrf`; PATCH y DELETE declaran además `@method(...)`. Las salidas usan `{{ }}`, por lo que Blade aplica escape HTML.

## 11. Frontend

### `public/css/app.css`

Define variables de marca, navegación, portada, catálogo, autenticación, carrito, checkout, factura, administración y pie. Incluye puntos de quiebre para tabletas y teléfonos y respeta `prefers-reduced-motion`.

### `public/js/app.js`

- alterna tarjeta y PayPal;
- cambia `required` para no validar campos ocultos;
- formatea el número de tarjeta;
- formatea la vigencia `MM/AA`.

JavaScript mejora la experiencia, pero la seguridad depende siempre de la validación del servidor.

## 12. Pruebas

- `CartTotalsTest`: IVA, envío y límites monetarios.
- `RecentProductsServiceTest`: saneamiento, orden, deduplicación y límite de cookie.
- `AuthenticationTest`: registro, normalización, hash, login, logout y rate limiting.
- `CartTest`: agregar, cambiar, eliminar, cantidades inválidas e inventario.
- `CatalogAndCookieTest`: búsqueda, filtros, cookie corrupta, inactivos y XSS.
- `CheckoutTest`: tarjeta, Luhn, vigencia, PayPal, inventario, seguimiento y privacidad.
- `EndToEndPurchaseTest`: recorrido completo de compra dentro de una misma sesión.
- `OrderManagementTest`: estados, cancelación idempotente, pago e inventario.
- `PaymentGatewaySafetyTest`: bloqueo de simulación en producción.
- `ProfileAndReportsTest`: perfil, historial, rol, validación y PDF.
- `SecurityHardeningTest`: cabeceras, HTTPS, caché y permisos.

`RefreshDatabase` reconstruye SQLite en memoria para cada prueba de integración. El resultado verificable se obtiene con:

```powershell
php artisan test
php vendor/bin/pint --test
```

## 13. Archivos de entorno y despliegue

- `.env.example`: XAMPP/MariaDB principal.
- `.env.sqlite.example`: compatibilidad SQLite.
- `deployment/apache-vhost.conf.example`: DocumentRoot seguro hacia `public`.
- `deployment/apache-ssl-vhost.conf.example`: redirección HTTPS y certificado.
- `deployment/env.production.example`: variables seguras sin secretos reales.
- `.github/workflows/tests.yml`: pruebas automáticas para GitHub Actions.
- `.github/dependabot.yml`: propuestas mensuales de actualización.
- `SECURITY.md`: política y condiciones antes de aceptar clientes reales.
- `.gitignore`: excluye `.env`, dependencias, archivos temporales y paquetes de entrega.

## 14. Cómo explicar seguridad en la exposición

1. Eloquent parametriza las consultas y reduce el riesgo de inyección SQL.
2. Blade escapa contenido para reducir XSS.
3. Cada formulario de cambio lleva CSRF.
4. Las contraseñas usan hash automático.
5. El login regenera la sesión y el logout la invalida.
6. Middleware separa cliente y administrador.
7. Facturas comprueban propietario.
8. Checkout bloquea inventario dentro de una transacción.
9. Tarjeta completa, vigencia y CVV no se persisten ni quedan en `old input`.
10. CSP limita recursos y las páginas privadas prohíben caché.
11. Los estados no saltan etapas y cancelar repone stock una sola vez.
12. Producción fuerza HTTPS, añade HSTS y bloquea pagos simulados por defecto.

## 15. Comentarios y código limpio

Los comentarios agregados explican intención, seguridad y reglas que no son obvias. No se comenta literalmente cada etiqueta o asignación porque comentarios como "suma uno" o "muestra un botón" duplican el código y dificultan el mantenimiento. Los nombres de clases, métodos y variables explican las operaciones simples; esta guía conserva la explicación completa del sistema.
