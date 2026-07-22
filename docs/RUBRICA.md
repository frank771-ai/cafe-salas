# Matriz de cumplimiento de la rúbrica

| N.º | Aspecto | Evidencia / estado |
|---:|---|---|
| 1 | Entrega a tiempo | El paquete final queda preparado; la carga puntual corresponde al equipo. |
| 2 | Nombre `ProyectoFinalNombreEstudiantes` | Se genera `ProyectoFinalNombreEstudiantes.zip`; reemplace el marcador por los nombres o apellidos reales. |
| 3 | Autenticación y usuarios | `AuthController`, middleware `auth`, sesiones cifradas. |
| 4 | Registro | Rutas `/registro`, validación y prueba automatizada. |
| 5 | Login/logout | Rutas, regeneración/invalidez de sesión y rate limit. |
| 6 | Perfil e historial | `ProfileController` y `profile/show.blade.php`. |
| 7 | Categorías | `Category`, relación y filtro. |
| 8 | Lista/detalles/imágenes/precio | Catálogo, detalle y ocho SVG locales. |
| 9 | Búsqueda/filtros | Nombre/descripción, categoría, precio y orden. |
| 10 | Carrito CRUD | `CartService` y `CartController`. |
| 11 | Impuesto/envío | IVA 13 %, ₡2.500 y envío gratis desde ₡30.000. |
| 12 | Compra/factura | `orders`, `order_items`, factura web/PDF con ID, fecha y montos. |
| 13 | Tarjeta/PayPal | Formulario condicional y validación; simulación documentada. |
| 14 | Confirmación/seguimiento | `order_number`, `tracking_number` y confirmación. |
| 15 | Reportes de ventas | PDF mensual y por cliente. |
| 16 | PHP y base de datos | PHP/Laravel; MySQL/phpMyAdmin principal y SQLite para pruebas/perfil alternativo. |
| 17 | Frontend | Bootstrap, CSS propio, SVG y jerarquía visual. |
| 18 | Validación | Form Request, reglas por campo, CSRF y mensajes. |
| 19 | Cookie reciente | Cookie cifrada `recent_products`, 30 días. |
| 20 | Mostrar recientes | Sección en inicio, preserva orden de visita. |
| 21 | Código completo | Aplicación, dependencias declaradas, SQL, pruebas y documentación. |
| 22 | Instrucciones de uso | README, manual MD y DOCX. |
| 23 | Documento de pruebas | `docs/PRUEBAS.md`, auditoría y 43 pruebas/197 aserciones en dos motores. |
| 24 | Exposición | Guion preparado; la asistencia corresponde al equipo. |
| 25 | Funcionalidades | Matriz completa y pruebas verdes. |
| 26 | Responsive/UX | Bootstrap, breakpoints, accesibilidad y validación visual móvil. |
| 27 | Seguridad | Hash, ORM, CSRF, CSP, HSTS, rate limiting, privacidad de pagos, sesiones y autorización. |
| 28 | Calidad | MVC, servicios, relaciones tipadas, Pint y pruebas. |
| 29 | Pregunta docente 1 | Banco de preguntas y respuestas en guía de exposición. |
| 30 | Pregunta docente 2 | Banco de preguntas y respuestas en guía de exposición. |
| 31 | Pregunta docente 3 | Banco de preguntas y respuestas en guía de exposición. |
| 32 | GitHub | Git, `.gitignore`, CI, `composer.lock`, Dependabot y política de seguridad. Falta autenticar la cuenta y asociar el remoto. |

## Observaciones honestas

Los puntos 1, 24 y 29-31 dependen de acciones humanas. El pago es una simulación segura porque no se entregaron credenciales comerciales. El despliegue público, dominio y certificado dependen de una cuenta de hosting; la configuración y el procedimiento quedan documentados.
