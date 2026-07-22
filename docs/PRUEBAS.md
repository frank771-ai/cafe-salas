# Documento de pruebas - Origen Tico

## Objetivo

Verificar reglas del negocio, seguridad, persistencia, permisos y recorridos principales. En CI las pruebas usan SQLite en memoria; el funcionamiento real también fue validado con MariaDB de XAMPP.

## Ejecución

```powershell
php artisan test
php artisan test --filter=CheckoutTest
php vendor/bin/pint --test
```

Resultado de referencia: **17 pruebas aprobadas y 67 aserciones**.

## Casos automatizados

| Archivo | Casos cubiertos |
|---|---|
| `CartTotalsTest` | IVA, costo de envío y umbral de envío gratis. |
| `AuthenticationTest` | Registro, hash de contraseña, login, logout, correo duplicado y clave débil. |
| `CatalogAndCookieTest` | Búsqueda/filtros, cookie reciente y escape XSS. |
| `CartTest` | Agregar, actualizar, eliminar, total y límite por inventario. |
| `CheckoutTest` | Compra con tarjeta, algoritmo de Luhn, seguimiento, inventario, no almacenar tarjeta, PayPal y autorización de factura. |
| `ProfileAndReportsTest` | Perfil, historial, permisos administrativos y PDF mensual/cliente. |

## Pruebas manuales para la exposición

1. Filtrar `Tarrazú` entre ₡8.000 y ₡9.000.
2. Abrir dos productos y volver al inicio; confirmar “Vistos recientemente”.
3. Agregar cantidades, actualizar y eliminar en el carrito.
4. Confirmar que aparecen subtotal, IVA 13 %, envío y total.
5. Comprar con tarjeta de prueba `4111 1111 1111 1111`, fecha futura y CVV `123`.
6. Mostrar seguimiento, factura e historial.
7. Ingresar como administrador, cambiar estado y generar ambos reportes.
8. Abrir phpMyAdmin y mostrar `users`, `products`, `orders`, `order_items` y `payments`.

## Datos que no deben probarse en producción

No use tarjetas reales. La pasarela es simulada. Nunca comparta `.env`, `APP_KEY` ni credenciales de un hosting.
