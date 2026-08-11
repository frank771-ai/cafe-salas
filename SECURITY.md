# Política de seguridad

## Alcance

La versión académica de Café Salas recibe correcciones de seguridad en la rama principal del repositorio. No deben enviarse tarjetas, contraseñas, llaves, archivos `.env` ni datos personales reales en reportes públicos.

## Reportar una vulnerabilidad

Comunique el problema de forma privada al equipo del curso e incluya la ruta afectada, los pasos mínimos para reproducirlo y el impacto observado. No publique detalles explotables antes de que exista una corrección.

## Condiciones para producción

Antes de aceptar clientes reales se debe:

1. integrar una pasarela certificada y mantener `ALLOW_SIMULATED_PAYMENTS_IN_PRODUCTION=false`;
2. usar HTTPS con un certificado válido y `SESSION_SECURE_COOKIE=true`;
3. generar un `APP_KEY` exclusivo y mantener `.env` fuera de Git;
4. proteger el archivo SQLite con permisos del sistema y copias de respaldo;
5. ejecutar `composer audit`, las pruebas y las copias de respaldo;
6. configurar monitoreo, correo transaccional y un procedimiento de incidentes.

La aplicación bloquea pagos simulados en `APP_ENV=production` salvo que se habiliten explícitamente. Esto evita presentar una demostración académica como una pasarela comercial.
