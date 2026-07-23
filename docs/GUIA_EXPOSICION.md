# Guía de exposición y preguntas

## Demostración sugerida (10 minutos)

1. **Problema y arquitectura (1 min):** describir la tienda y mostrar carpetas MVC.
2. **Catálogo (1 min):** categorías, búsqueda y filtros.
3. **Cookie (1 min):** abrir productos y mostrar “Vistos recientemente”.
4. **Carrito (1 min):** agregar, modificar, eliminar y explicar IVA/envío.
5. **Autenticación/perfil (1 min):** registro, login e historial.
6. **Compra (2 min):** tarjeta o PayPal, transacción, confirmación y factura.
7. **Administración (1 min):** métricas, estados y reportes PDF.
8. **phpMyAdmin (1 min):** relaciones y registros de pedido/pago.
9. **Seguridad/pruebas/GitHub (1 min):** ejecutar tests y mostrar Actions.

## Preparación obligatoria ante las sanciones

El enunciado permite anular hasta el 75 % del proyecto si un estudiante no responde al menos la mitad de las preguntas. Antes de exponer, cada integrante debe ejecutar por sí mismo el recorrido de compra, explicar un controlador, una validación, una relación de base de datos y una prueba. También debe reconocer la asistencia de Codex y describir qué adaptó y verificó; la atribución no reemplaza la comprensión.

## Preguntas probables

### ¿Qué es MVC y dónde se aplica?

Modelo representa y consulta datos; Vista presenta HTML con Blade; Controlador procesa la solicitud y coordina reglas. Ejemplo: `Product` consulta, `ProductController@index` filtra y `products/index.blade.php` muestra.

### ¿Por qué usar migraciones y no crear tablas solo en phpMyAdmin?

phpMyAdmin administra y permite observar/importar la base. Las migraciones versionan el esquema en código, permiten reproducirlo en cada equipo, revierten cambios y facilitan GitHub/CI.

### ¿Qué hacen `$fillable` y los casts?

`$fillable` limita los campos asignables en masa. Los casts convierten automáticamente valores, por ejemplo `is_admin` a booleano y fechas a objetos Carbon.

### ¿Cómo se evita inyección SQL?

Eloquent genera consultas parametrizadas. No se concatenan entradas del usuario en SQL crudo. Además, todos los filtros se validan.

### ¿Cómo se evita XSS y CSRF?

Blade escapa `{{ $valor }}`. Cada formulario mutable contiene `@csrf`, y Laravel rechaza tokens ausentes o inválidos.

### ¿Por qué el carrito está en sesión?

Permite que visitantes seleccionen productos sin crear una cuenta y reduce tablas temporales. Al comprar se reconstruyen precios e inventario desde la base, por seguridad.

### ¿Cómo se calcula el total?

Subtotal es suma de precio por cantidad; IVA es subtotal por 0,13; envío cuesta ₡2.500 salvo subtotal igual o mayor a ₡20.000; total suma los tres valores.

### ¿Por qué se usa una transacción?

Pedido, detalles, pago e inventario deben confirmarse juntos. Si una parte falla, todo se revierte y no queda una compra incompleta.

### ¿Se almacenan tarjetas?

No. Se validan solo en la solicitud y se descartan. El pago conserva referencia simulada y últimos cuatro dígitos; nunca CVV ni número completo.

### ¿MySQL o SQLite?

La ejecución de clase usa MariaDB/MySQL de XAMPP y phpMyAdmin. Las migraciones son portables y PHPUnit usa SQLite en memoria para pruebas aisladas; también existe `.env.sqlite.example` por el requisito escrito.

### ¿Cómo funciona la cookie reciente?

Guarda un JSON con hasta seis IDs, dura 30 días y Laravel la cifra y marca HttpOnly/SameSite. Al volver al inicio, se consultan solo productos activos.

### ¿Qué diferencia hay entre autenticación y autorización?

Autenticación comprueba quién es el usuario; autorización decide qué puede hacer. Un cliente autenticado no puede abrir facturas ajenas ni reportes administrativos.

### ¿Cómo se publica con HTTPS?

Se sube a un hosting con PHP/MySQL, se apunta el dominio, se emite Let's Encrypt, se configura `APP_URL=https://...`, `APP_ENV=production`, `APP_DEBUG=false` y `SESSION_SECURE_COOKIE=true`.

### ¿Qué adaptación demuestra que el catálogo fue revisado?

El chocolate se corrigió a 82 % de cacao y la caja se renombró para representar las ocho regiones cafetaleras de Costa Rica. Una migración actualiza instalaciones existentes, mientras el seeder y el respaldo SQL garantizan que una instalación nueva tenga los mismos datos.

### ¿Cómo se controlan vulnerabilidades de dependencias?

`composer audit --locked` compara `composer.lock` con avisos publicados. Durante la revisión se actualizó Dompdf de 3.1.0 a 3.1.6 porque la versión anterior tenía seis avisos; después se repitieron los reportes PDF y toda la batería de pruebas.
