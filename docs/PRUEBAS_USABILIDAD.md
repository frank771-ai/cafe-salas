# Pruebas de capa 8 y usabilidad - Origen Tico

Fecha de ejecución: 22 de julio de 2026.

## Objetivo y alcance

La “capa 8” se usa aquí en su sentido informal: comprobar cómo una persona entiende y opera el sistema, además de verificar que el código responda correctamente. La revisión cubrió lenguaje, continuidad de tareas, prevención y recuperación de errores, accesibilidad semántica, claridad visual y retroalimentación.

Las pruebas se hicieron sobre la aplicación Laravel realmente ejecutada en `http://127.0.0.1:8000`, con MariaDB de XAMPP y una base temporal aislada. La base principal `origen_tico` no fue modificada y conservó sus 3 usuarios, 8 productos y 3 pedidos.

## Perfiles y recorridos ejecutados

| Perfil | Recorrido | Resultado final |
|---|---|---|
| Visitante | Inicio, catálogo, búsqueda, filtro de precio, detalle, cookie de recientes y carrito | Aprobado |
| Visitante | Registro con datos inválidos y luego registro válido | Aprobado |
| Visitante a cliente | Carrito, solicitud de compra, inicio de sesión y retorno automático al pago | Aprobado después de corrección |
| Cliente | Tarjeta inválida, tarjeta válida, total, confirmación, seguimiento, factura y PDF | Aprobado |
| Cliente | Perfil e historial de pedidos | Aprobado |
| Administrador | Panel, métricas, inventario bajo, transición de estado y factura | Aprobado |
| Administrador | Reporte mensual PDF y formulario de reporte por cliente | Aprobado |

## Hallazgos y correcciones

| Hallazgo observado en navegador | Impacto humano | Corrección aplicada | Reprueba |
|---|---|---|---|
| “Ingresar y comprar” enviaba al inicio después del login | El usuario perdía el punto del proceso de compra | El enlace solicita directamente `/comprar`; el middleware de autenticación conserva y recupera la URL prevista | Aprobada: vuelve a “Finalizar compra” |
| Reglas inválidas mostraban `validation.regex`, `validation.password.mixed` y otras claves | Mensajes incomprensibles para una persona no técnica | Catálogo completo de validaciones en español y nombres legibles de campos | Aprobada: todos los mensajes probados son claros |
| Un precio máximo sin precio mínimo era rechazado | Un filtro válido parecía no funcionar | La comparación entre máximo y mínimo solo se aplica cuando se indicó el mínimo | Aprobada: ₡9.000 máximo devuelve Tarrazú |
| Todos los botones de tarjetas se llamaban “Agregar” para lector de pantalla | No era posible distinguir qué producto se agregaría | Nombre accesible incluye el producto | Aprobada: “Agregar Tarrazú Reserva 340 g al carrito” |
| El carrito anunciaba siempre “artículos” | Frase incorrecta con una unidad | Singular/plural dinámico | Aprobada: “Carrito con 1 artículo” |
| La navegación activa dependía solo del color | Faltaba contexto semántico | Se agregó `aria-current="page"` | Aprobada por prueba de HTML |
| Cantidad y existencia estaban visualmente próximas, pero no asociadas | Un lector de pantalla podía omitir el límite disponible | `aria-describedby` enlaza la cantidad con el inventario | Aprobada por prueba de HTML |
| El catálogo decía “1 resultados” | Error de redacción visible | Singular/plural dinámico y lenguaje comercial más natural | Aprobada: “1 producto” |

## Validaciones humanas comprobadas

- Contraseña corta, sin mayúscula/minúscula, sin número y confirmación diferente.
- Teléfono con formato inválido.
- Tarjeta con longitud inválida, fallo de Luhn, vencimiento pasado y CVV corto.
- Los campos de tarjeta quedan vacíos después del error y no se conservan en sesión.
- Cantidad de producto limitada por el inventario disponible.
- Filtros combinados y precio máximo sin mínimo.
- Transición administrativa permitida de `Pagado` a `Preparando`.

## Accesibilidad y claridad visual

- Enlace “Saltar al contenido”, regiones `nav`, `main`, `aside` y `footer` identificables.
- Formularios con etiquetas asociadas; controles de pago, perfil, registro y filtros tienen nombres accesibles.
- Encabezado principal único y jerarquía de títulos comprensible en las pantallas recorridas.
- Imágenes de producto con texto alternativo; decoraciones marcadas para no generar ruido.
- Estados de éxito anunciados de forma no intrusiva y errores agrupados como alerta.
- La portada y el catálogo se comprobaron en 1440 × 900 y 390 × 844; no presentaron superposición, texto cortado, acciones ocultas ni desplazamiento horizontal.
- La segunda revisión confirmó fotografías cargadas, catálogo completamente en español, precios en colones, botones de compra contrastantes y el nuevo umbral de envío gratuito de ₡20.000.
- La interfaz renovada mantiene foco visible, contraste legible, texto alternativo y adaptaciones específicas para tableta, teléfono y preferencia de movimiento reducido. Antes de una publicación comercial se recomienda repetir en teléfonos físicos y con un lector de pantalla real; esta revisión no pretende ser una certificación WCAG formal.

## Evidencia automatizada

Se agregó `tests/Feature/UsabilityTest.php` para impedir regresiones en:

1. Retorno al pago después del inicio de sesión.
2. Precio máximo opcional sin precio mínimo.
3. Mensajes de validación en español, sin claves internas.
4. Contexto accesible de navegación, carrito, producto y cantidad.

Resultado final en ambos motores:

```text
SQLite en memoria: 48 pruebas, 233 aserciones, 0 fallos
MariaDB de XAMPP:   48 pruebas, 233 aserciones, 0 fallos
Laravel Pint:       aprobado
```

## Dictamen

Los recorridos académicos y de demostración quedaron utilizables y consistentes. El proyecto está listo para entrega y exposición. Para un mercado real todavía deben incorporarse pasarela certificada, HTTPS público, monitoreo, correo transaccional, copias de respaldo, pruebas en dispositivos físicos y una evaluación formal de accesibilidad con usuarios.
