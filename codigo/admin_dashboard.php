<?php
session_start();
require_once "conexion.php";
require_once "funciones_admin.php";

// Validar sesión
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

// Desactivar usuario
if (isset($_GET['desactivar'])) {
    desactivarUsuario($conexion, $_GET['desactivar']);
    header("Location: admin_dashboard.php");
    exit;
}

// Obtener lista de usuarios
$usuarios = obtenerUsuarios($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="../estilos/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <h2>
        <i class="fa-solid fa-user-shield"></i> 
        Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> 
        <small>(Panel del Administrador)</small>
    </h2>

    <h3><i class="fa-solid fa-users"></i> Usuarios registrados</h3>
    <table>
        <tr>
            <th><i class="fa-solid fa-id-badge"></i> ID</th>
            <th><i class="fa-solid fa-user"></i> Nombre completo</th>
            <th><i class="fa-solid fa-envelope"></i> Correo</th>
            <th><i class="fa-solid fa-user-tag"></i> Tipo</th>
            <th><i class="fa-solid fa-toggle-on"></i> Estado</th>
            <th><i class="fa-solid fa-gears"></i> Acción</th>
        </tr>
        <?php while ($fila = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?= $fila['id'] ?></td>
                <td><?= htmlspecialchars($fila['nombre'] . " " . $fila['apellido']) ?></td>
                <td><?= htmlspecialchars($fila['correo']) ?></td>
                <td><?= htmlspecialchars($fila['tipo']) ?></td>
                <td><?= htmlspecialchars($fila['estado']) ?></td>
                <td>
                    <?php if ($fila['tipo'] != 'administrador' && $fila['estado'] != 'Inactivo'): ?>
                        <a class="btn-accion" href="?desactivar=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro que desea desactivar este usuario?');">
                            <i class="fa-solid fa-user-slash"></i> Desactivar
                        </a>
                    <?php else: ?>
                        <span class="text-muted"><i class="fa-solid fa-ban"></i> No disponible</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p>
        <a class="btn-accion" href="crear_admin.php">
            <i class="fa-solid fa-user-plus"></i> Crear nuevo administrador
        </a>
    </p>

    <hr>
    <p>
        <a class="btn-salir" href="cerrar_sesion.php">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
        </a>
    </p>

</body>
</html>


