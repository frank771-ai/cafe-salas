# Auditoría de lanzamiento - Café Salas

Fecha de revisión final: 25 de julio de 2026.

## Dictamen

El proyecto está **apto para entrega y demostración académica**. La lógica se verificó con SQLite: **50 pruebas y 242 aserciones aprobadas**.

No debe anunciarse todavía como comercio productivo con cobros reales. Para salir al mercado faltan credenciales de una pasarela certificada, dominio/hosting, certificado público, correo transaccional, monitoreo y responsables de operación. Esos puntos dependen de cuentas externas y no pueden simularse honestamente.

## Funciones verificadas

| Área | Evidencia automatizada |
|---|---|
| Autenticación | Registro, normalización, hash, login, logout, contraseñas débiles, duplicados y límite de intentos. |
| Perfil | Edición, correo único, protección de rol e historial de pedidos. |
| Catálogo | Categorías, detalle, imágenes, búsqueda, precios, orden, productos inactivos y escape XSS. |
| Cookies | Cifrado de Laravel, contenido malformado, deduplicación y máximo de seis IDs. |
| Carrito | Agregar, actualizar, eliminar, cantidades inválidas, stock e IVA/envío en límites. |
| Compra | Tarjeta Luhn, vencimiento, PayPal, transacción, cambio de inventario y carrito vacío. |
| Privacidad de pago | Número, vencimiento y CVV no se almacenan ni se conservan como entrada anterior. |
| Pedidos | Confirmación, seguimiento, factura, propiedad, estados válidos y cancelación idempotente. |
| Administración | Rol, panel, devolución de inventario, reembolso simulado y reportes PDF. |
| Transporte | CSP, HSTS bajo HTTPS, `nosniff`, anti-frame, Referrer Policy y páginas privadas sin caché. |
| Recorrido completo | Registro hasta compra, confirmación, factura PDF e historial dentro de una misma sesión. |
| Capa 8 y usabilidad | Navegación real, continuidad carrito-login-pago, lenguaje de errores, nombres accesibles y filtros opcionales. |

## Comandos ejecutados

```powershell
php artisan test
php vendor/bin/pint --test
php artisan view:cache
php artisan route:cache
php artisan config:cache
composer validate --strict
```

`composer audit --locked` detectó seis avisos que afectaban Dompdf 3.1.0. La dependencia se actualizó a **Dompdf 3.1.6**, se regeneró `composer.lock` y una segunda auditoría confirmó **cero avisos de seguridad**. Los reportes PDF y la batería completa se volvieron a probar después de la actualización.

## Controles añadidos durante la auditoría

- Campos de tarjeta excluidos del `old input` de Laravel.
- Content Security Policy y prohibición de caché para páginas autenticadas.
- Normalización de nombre, correo, teléfono y dirección.
- Rate limiting en registro, login, carrito, perfil, checkout, estados y reportes.
- Flujo de estados sin saltos; cancelación devuelve stock una sola vez.
- Pagos simulados bloqueados por defecto en producción.
- Configuración de Apache SSL, ambiente productivo, Dependabot y política de seguridad.
- Mensajes de validación completos en español y correcciones de continuidad, singular/plural y contexto accesible.
- Catálogo corregido a chocolate oscuro 82 % y caja de las ocho regiones cafetaleras.
- Documento de atribución y guía de defensa ampliados según las sanciones indicadas en el enunciado.

## Lista obligatoria antes de producción real

1. Seleccionar PayPal, Stripe u otro proveedor y completar su verificación comercial.
2. Reemplazar `SimulatedPaymentGateway` por el SDK, webhooks firmados e idempotencia del proveedor.
3. Contratar/configurar hosting, dominio, HTTPS válido y copias de respaldo cifradas.
4. Crear credenciales MariaDB de mínimo privilegio; nunca usar `root` fuera de XAMPP local.
5. Configurar correo, alertas, registros centralizados, disponibilidad y respuesta a incidentes.
6. Ejecutar una prueba de carga y una revisión de seguridad autorizada sobre el dominio final.
7. Revisar GitHub Actions, agregar a la docente como colaboradora y activar protección de rama.

## Relación con la rúbrica

Los criterios técnicos 3 al 23, 25 al 28 y 32 cuentan con código y evidencia. El repositorio privado real es `Byroncha1323/cafe-salas`. Los criterios 1, 24 y 29 al 31 dependen de entrega, exposición y respuestas humanas. SQLite es la base principal del criterio 16; MariaDB/phpMyAdmin es opcional.
