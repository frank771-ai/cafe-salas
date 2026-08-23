# Informe de pruebas — Café Salas

Última ejecución: 23 de agosto de 2026. Entorno principal: PHP 8.2, Laravel 12 y SQLite.

## Resultado automatizado

| Comprobación | Resultado |
|---|---|
| `composer install --no-interaction --prefer-dist` | Correcto; lock verificado y dependencias instaladas. |
| `php artisan optimize:clear` | Correcto |
| `php artisan migrate:fresh --seed` | Correcto sobre `database/database.sqlite` |
| `php artisan route:list --except-vendor` | 24 rutas de aplicación |
| `php artisan test` | **52 pruebas, 250 aserciones, 0 fallos** |
| `php vendor/bin/pint --test` | Correcto |
| `composer audit --locked --no-interaction` | Correcto; 0 vulnerabilidades conocidas |

La auditoría consulta un registro externo y representa el estado conocido en la fecha indicada; GitHub Actions la repite en cada cambio.

## Cobertura

- Autenticación: registro, normalización, contraseña fuerte, hash, login, logout y limitación de intentos.
- Catálogo: categorías, búsqueda, precios, orden, productos inactivos, XSS e imágenes/datos sembrados.
- Cookies: saneamiento, duplicados, límite, contenido malformado y sección de recientes.
- Carrito: agregar, actualizar, eliminar, inventario y cantidades inválidas.
- Totales: subtotal, IVA 13 %, envío de ₡2.500 y envío gratuito desde ₡20.000.
- Compra: tarjeta con Luhn, fecha, PayPal, transacción, inventario, privacidad, pedido `CS-...` y seguimiento.
- Perfil/facturas: propietario, historial y PDF.
- Administración: rol, estados, cancelación idempotente, reintegro de inventario y reportes.
- Seguridad: CSRF, CSP, HSTS, caché privada, permisos y rechazo de pasarela simulada en producción.
- Identidad: título exacto de Café Salas y ausencia visible del nombre anterior.
- Errores: credenciales incorrectas conservan solo el correo seguro y las rutas inexistentes responden 404.
- Recorrido integral: registro, selección, compra, factura e historial.

## Pruebas manuales y de usabilidad

1. Ejecutar `php artisan migrate:fresh --seed` y `php artisan serve`.
2. Revisar inicio, catálogo, detalle, recientes y carrito en escritorio y móvil.
3. Registrarse con un correo nuevo y comprobar errores deliberados.
4. Comprar con la tarjeta académica `4111 1111 1111 1111`, fecha futura y CVV `123`.
5. Confirmar pedido, seguimiento, factura e historial.
6. Ingresar como administrador y descargar ambos reportes.
7. Confirmar que ningún formulario muestra o conserva el PAN/CVV.

Los pagos son simulados; estas pruebas no realizan cargos reales.

La revisión de usabilidad también comprobó navegación clara, mensajes de validación en español, controles con nombres accesibles, retorno al checkout después del login y adaptación del catálogo, formularios y tablas a pantallas pequeñas. El 10 de agosto se recorrió manualmente en navegador el flujo catálogo → filtro → producto reciente → carrito → login → tarjeta simulada → confirmación → perfil, además del panel administrativo y los reportes. El 23 de agosto se repitió la suite completa y se confirmó que la copia local coincide con la rama final `codex/proyecto-final` del repositorio `frank771-ai/cafe-salas`.
