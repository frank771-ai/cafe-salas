# Matriz final de cumplimiento — Café Salas

Proyecto de Byron Chacón y Franklin Castillo para la Ing. Milena Vargas Blanco. La columna “Estado” distingue lo verificable técnicamente de las acciones que solo pueden completar los estudiantes.

| N. | Criterio | Evidencia o corrección aplicada | Estado |
|---:|---|---|---|
| 1 | Entrega a tiempo | ZIP final preparado; la carga en plataforma depende del equipo. | Acción humana |
| 2 | Carpeta comprimida identificada | `ProyectoFinal-ByronChacon-FranklinCastillo.zip`. | Cumple al entregar |
| 3 | Autenticación y usuarios | `AuthController`, modelo `User`, middleware y sesiones. | Cumple |
| 4 | Registro | Vista, validación, hash y prueba automatizada. | Cumple |
| 5 | Login y logout | Regeneración/invalidez de sesión y limitación de intentos. | Cumple |
| 6 | Perfil e historial | `ProfileController`, edición validada y pedidos paginados. | Cumple |
| 7 | Categorías | `Category`, relación con productos y filtro. | Cumple |
| 8 | Productos detallados | Descripción, precio CRC, imagen, inventario y categoría. | Cumple |
| 9 | Búsqueda y filtros | Nombre, categoría, precio mínimo/máximo y orden. | Cumple |
| 10 | Carrito | Agregar, actualizar y eliminar con validación de inventario. | Cumple |
| 11 | Impuesto y envío | IVA 13 %, envío y envío gratuito calculados por `CartService`. | Cumple |
| 12 | Compra/factura | Usuario, fecha, partidas, montos, factura web y PDF. | Cumple |
| 13 | Proceso de pago | Tarjeta y PayPal simulados; validación Luhn y correo. | Cumple |
| 14 | Confirmación/seguimiento | Pedido `CS-...` y seguimiento `CRPOST-...` únicos. | Cumple |
| 15 | Reportes de ventas | PDF mensual y por cliente, protegidos para administrador. | Cumple |
| 16 | PHP y SQLite | PHP/Laravel con `database/database.sqlite` como base principal. | Cumple |
| 17 | Frontend adecuado | Blade, Bootstrap, CSS propio e imágenes reales. | Cumple |
| 18 | Validación de entradas | Form Requests, reglas, mensajes, CSRF y escape Blade. | Cumple |
| 19 | Cookies de recientes | Cookie cifrada `recent_products`, máximo seis IDs. | Cumple |
| 20 | Mostrar recientes | Sección de productos visitados en inicio. | Cumple |
| 21 | Código completo | Código, migraciones, seeders, pruebas y documentación. | Cumple |
| 22 | Documentación detallada | README, manuales, guía y DOCX con instalación/uso. | Cumple |
| 23 | Documento de pruebas | `docs/PRUEBAS.md` y suite PHPUnit reproducible. | Cumple |
| 24 | Participación en exposición | Guía dividida entre ambos estudiantes. | Acción humana |
| 25 | Todas las funcionalidades | Trazadas en esta matriz y verificadas mediante pruebas. | Cumple técnicamente |
| 26 | Responsive y UX | Navegación, formularios, tablas y catálogo adaptables. | Cumple |
| 27 | Seguridad/datos sensibles | Hash, autorización, CSRF, sesión, headers y no almacenar CVV/PAN. | Cumple |
| 28 | Calidad y buenas prácticas | MVC, servicios, relaciones, transacciones, Pint y pruebas. | Cumple |
| 29 | Pregunta docente 1 | Banco de preguntas preparado; respuesta depende del estudiante. | Acción humana |
| 30 | Pregunta docente 2 | Banco de preguntas preparado; respuesta depende del estudiante. | Acción humana |
| 31 | Pregunta docente 3 | Banco de preguntas preparado; respuesta depende del estudiante. | Acción humana |
| 32 | GitHub | Git, `.gitignore`, workflow, Dependabot y repositorio remoto real documentado. | Cumple |

## Diagnóstico final honesto

Los criterios técnicos están implementados. No es posible garantizar desde el código los criterios 1, 24 y 29–31: Byron Chacón y Franklin Castillo deben entregar puntualmente, participar y responder demostrando comprensión. El pago es una simulación académica; convertirlo en cobro real requiere proveedor, credenciales y cumplimiento externo.
