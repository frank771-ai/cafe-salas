# Revisión, adaptación y atribución

El enunciado advierte que el uso de herramientas de inteligencia artificial sin comprensión, adaptación ni atribución puede penalizarse. Este proyecto fue asistido con Codex para generar, revisar y documentar código. El equipo debe declarar el apoyo de acuerdo con la política de la universidad y demostrar dominio del resultado.

## Adaptaciones realizadas por el equipo

- Tema elegido: tienda costarricense de café y productos artesanales.
- Base principal: MariaDB/MySQL de XAMPP administrada en phpMyAdmin.
- Compatibilidad SQLite: conservada para pruebas automáticas y revisión de la consigna.
- Reglas comerciales: IVA 13 %, envío de ₡2.500 y envío gratuito desde ₡20.000.
- Pago: pasarela académica sustituible; no realiza cobros reales.
- Seguridad adicional: bloqueo de inventario, algoritmo de Luhn, encabezados HTTP y privacidad de facturas.
- Diseño: identidad visual propia, responsive y accesible; las fotografías aportadas deben conservar evidencia de licencia antes de un uso comercial.

## Declaración sugerida

> Para apoyar el desarrollo y la revisión se utilizó OpenAI Codex. El equipo adaptó el resultado al caso Origen Tico, configuró XAMPP/phpMyAdmin, ejecutó las pruebas, revisó el código y conserva documentación técnica para explicar cada decisión. No se presentan cobros reales ni servicios externos como implementados cuando requieren credenciales.

El equipo puede ajustar esta redacción, pero no debería eliminar una atribución requerida por la normativa académica.

## Lista de comprensión antes de exponer

Cada integrante debe poder explicar y demostrar:

1. Qué diferencia existe entre ruta, controlador, modelo y vista.
2. Cómo se conecta `.env` con la base `origen_tico` de XAMPP.
3. Cómo una migración crea tablas y cómo un seeder carga datos.
4. Por qué Eloquent ayuda contra inyección SQL.
5. Por qué `{{ }}` protege las vistas frente a XSS.
6. Qué hacen CSRF, hash de contraseña y regeneración de sesión.
7. Dónde se guarda el carrito y cómo se calculan IVA y envío.
8. Por qué checkout usa transacción y `lockForUpdate()`.
9. Qué información de tarjeta se descarta y qué información se guarda.
10. Cómo se genera una factura y un reporte PDF.
11. Cómo funciona la cookie de productos recientes.
12. Qué protegen `auth`, `admin` y la comprobación de propietario.
13. Cómo ejecutar pruebas y leer una falla.
14. Qué archivos se suben a GitHub y por qué `.env` no se publica.

La guía `CODIGO_EXPLICADO.md` contiene las respuestas técnicas para estudiar estos puntos.
