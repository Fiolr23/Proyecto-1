## Instrucciones rápidas para agentes AI (Proyecto-1)

Breve: este proyecto es una aplicación PHP + MySQL (mysqli) para gestionar "rides" (viajes), usuarios (pasajero/chofer/administrador), reservas y vehículos. Aquí están los patrones y atajos que hacen que un agente sea productivo inmediatamente.

- Stack y ejecución
  - Código: PHP 7/8, plantillas PHP simples (no framework). Base de datos MySQL/MariaDB.
  - Ejecutar localmente: usar XAMPP/WAMP o el servidor embebido de PHP apuntando la carpeta raíz a `httpdocs/Proyecto1`.
  - Credenciales DB: `codigo/conexion.php` (host, usuario, contrasena, base_datos). Cambiar aquí para entorno de desarrollo.

- Dependencias y envío de correo
  - PHPMailer está incluido en `PHPMailer/` (no se necesita composer para este repo tal como está). Ejemplo de uso: `codigo/procesar_registro.php` carga `PHPMailer/src/PHPMailer.php`.
  - Atención: credenciales SMTP están en claro en `procesar_registro.php`. No las cambies en commits públicos — usa variables de entorno si se hace un cambio real.

- Autenticación y roles
  - Sesiones: se inicia con `session_start()` (ver `codigo/procesar_login.php`, `codigo/reservar_ride_form.php`).
  - Tipos de usuario: los valores observados son `administrador`, `chofer`, `pasajero`. Código de ejemplo para redirección en `procesar_login.php`.
  - Estado de cuenta: `estado` puede ser `Pendiente`, `Inactivo` o activo; el login bloquea cuentas pendientes.

- Base de datos y seguridad práctica
  - Uso de prepared statements con mysqli: el proyecto ya lo usa (ej.: `procesar_registro.php`, `procesar_login.php`, consultas en `reservar_ride_form.php`). Mantén prepared statements al añadir/editar SQL.
  - Hash de contraseñas: `password_hash(..., PASSWORD_BCRYPT)` y `password_verify()` están en uso. No reemplazar por texto plano.

- Estructura y rutas clave (ejemplos)
  - Conexión DB: `codigo/conexion.php`
  - Registro + activación por correo: `codigo/procesar_registro.php`, `codigo/activar_cuenta.php`
  - Login: `codigo/procesar_login.php`, vista `codigo/login.php`
  - Reservas: `codigo/reservar_ride_form.php`, lógica en `codigo/reservar_funciones.php`
  - CRUD rides/vehículos: `ride_create.php`, `ride_edit.php`, `vehiculo_form.php`, `vehiculos.php`
  - Uploads: `uploads/choferes/`, `uploads/pasajeros/`, `uploads/vehiculos/` (ver manejo de archivos en `procesar_registro.php`)

- Convecciones de código y patrón común
  - Archivos PHP combinan lógica + vista en el mismo fichero. Mantener la mínima lógica en la plantilla y mover lógica reutilizable a `*_funciones.php` cuando sea posible.
  - Mensajes y redirecciones: a menudo se devuelven con JavaScript `alert()` + `window.location` o `header()` con query params (ver `procesar_registro.php`, `procesar_login.php`).
  - Localización/encoding: la conexión usa `set_charset("utf8")` en `conexion.php`.

- Cambios comunes y ejemplos rápidos
  - Añadir un campo en la tabla `rides`: actualizar la consulta SELECT en `reservar_ride_form.php` y formularios en `ride_create.php`/`ride_edit.php`.
  - Añadir validación de formulario: seguir el patrón de `procesar_registro.php` (validación POST, mostrar alert o volver atrás).
  - Arreglar fallo de permisos en uploads: verifica permisos de carpeta y crea directorio con `mkdir(..., 0777, true)` como ya se hace.

- Pruebas y debugging
  - No hay suite de tests; pruebas manuales en navegador + revisar logs de PHP/Apache.
  - Para debug rápido, activa display_errors en entorno local o añadir `error_log()` y revisar `php_error.log`.

- Seguridad y cuidado al editar
  - Evitar dejar credenciales (SMTP, DB) en commits. Si modificas `procesar_registro.php` o `conexion.php`, mueve valores sensibles a variables de entorno o a un archivo de configuración no versionado.
  - Siempre usar prepared statements para cambios en queries.

- Qué buscar al revisar PRs
  - Uso correcto de sesiones y verificación de rol antes de ejecutar acciones (ej.: `reservar_ride_form.php` verifica `$_SESSION['usuario_tipo']`).
  - No exponer credenciales ni tokens en HTML. Tokens de activación se generan con `bin2hex(random_bytes(32))` (ver `procesar_registro.php`).

Si algo no está claro o quieres que añada snippets concretos (por ejemplo: ejemplo de env var loader, extracción de SMTP a config, o un script de SQL para crear tablas), dime cuál y lo agrego.
