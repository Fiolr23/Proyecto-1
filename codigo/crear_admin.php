<?php
session_start();
require_once "conexion.php";
require_once "funciones_admin.php";

// Validar sesión
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

// Crear nuevo administrador
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = $_POST['contrasena'];

    $resultado = crearAdmin($conexion, $nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena);
    $mensaje = $resultado === true ? "Administrador creado exitosamente." : $resultado;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear nuevo administrador</title>
    <link rel="stylesheet" href="../estilos/formulario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

<div class="form-container">
    <h2><i class="fa-solid fa-user-plus"></i> Crear nuevo administrador</h2>

    <?php if ($mensaje): ?>
        <p class="mensaje <?= $resultado === true ? 'exito' : 'error' ?>">
            <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fa-solid fa-user"></i> Nombre:</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-user"></i> Apellido:</label>
            <input type="text" name="apellido" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-id-card"></i> Cédula:</label>
            <input type="text" name="cedula" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-calendar"></i> Fecha de nacimiento:</label>
            <input type="date" name="fecha_nacimiento" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-envelope"></i> Correo:</label>
            <input type="email" name="correo" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-phone"></i> Teléfono:</label>
            <input type="text" name="telefono" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-lock"></i> Contraseña:</label>
            <input type="password" name="contrasena" required>
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Crear administrador</button>
        <a href="admin_dashboard.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver </a>
    </form>
</div>

</body>
</html>


