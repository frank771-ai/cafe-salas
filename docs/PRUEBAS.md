# Documento de pruebas - Origen Tico

## Objetivo

Verificar reglas del negocio, seguridad, persistencia, permisos y recorridos principales. En CI las pruebas usan SQLite en memoria; el funcionamiento real también fue validado con MariaDB de XAMPP.

## Ejecución

```powershell
php artisan test
php artisan test --filter=CheckoutTest
php vendor/bin/pint --test
```

Resultado de referencia: **49 pruebas aprobadas y 237 aserciones** tanto en SQLite como en MariaDB temporal de XAMPP.

## Casos automatizados

| Archivo | Casos cubiertos |
|---|---|
| `CartTotalsTest` | IVA, costo de envío y umbral de envío gratis. |
| `AuthenticationTest` | Registro, normalización, hash, login, logout, duplicados, clave débil y rate limiting. |
| `CatalogAndCookieTest` | Búsqueda/filtros, cookie malformada, productos inactivos, escape XSS y nombres reales del catálogo. |
| `CartTest` | Agregar, actualizar, eliminar, cantidades inválidas, total y límite por inventario. |
| `CheckoutTest` | Tarjeta Luhn/vencida, privacidad de datos, transacción, stock, PayPal y autorización. |
| `EndToEndPurchaseTest` | Recorrido completo desde registro hasta factura PDF e historial. |
| `OrderManagementTest` | Estados permitidos, cancelación idempotente, reembolso y devolución de stock. |
| `PaymentGatewaySafetyTest` | Bloqueo de la pasarela simulada en producción. |
| `ProfileAndReportsTest` | Perfil, correo único, historial, permisos y PDF mensual/cliente. |
| `SecurityHardeningTest` | CSP, HSTS, cabeceras, caché privada, login y rol administrador. |
| `UsabilityTest` | Continuidad carrito-login-pago, filtros opcionales, mensajes en español y contexto accesible. |
| `RecentProductsServiceTest` | Saneamiento, deduplicación y límite de IDs en la cookie. |

## Pruebas manuales para la exposición

1. Filtrar `Tarrazú` entre ₡7.000 y ₡8.000.
2. Abrir dos productos y volver al inicio; confirmar “Vistos recientemente”.
3. Agregar cantidades, actualizar y eliminar en el carrito.
4. Confirmar que aparecen subtotal, IVA 13 %, envío y total.
5. Comprar con tarjeta de prueba `4111 1111 1111 1111`, fecha futura y CVV `123`.
6. Mostrar seguimiento, factura e historial.
7. Ingresar como administrador, cambiar estado y generar ambos reportes.
8. Abrir phpMyAdmin y mostrar `users`, `products`, `orders`, `order_items` y `payments`.

## Datos que no deben probarse en producción

No use tarjetas reales. La pasarela es simulada. Nunca comparta `.env`, `APP_KEY` ni credenciales de un hosting.
