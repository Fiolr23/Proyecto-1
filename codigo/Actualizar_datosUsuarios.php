<?php
session_start();
require_once "conexion.php";

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['usuario_id'];

// Obtener datos actuales del usuario
$sql = "SELECT nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, tipo FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Actualizar datos si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];

    // Manejar fotografía nueva (si se sube una)
    $nombreFoto = $usuario['fotografia'];
    if (!empty($_FILES['fotografia']['name'])) {
        $carpetaDestino = $usuario['tipo'] === "pasajero" ? "../uploads/pasajeros/" : "../uploads/choferes/";
        if (!file_exists($carpetaDestino)) mkdir($carpetaDestino, 0777, true);

        $extension = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
        $nombreFoto = $cedula . "_" . time() . "." . $extension;
        $rutaDestino = $carpetaDestino . $nombreFoto;
        move_uploaded_file($_FILES['fotografia']['tmp_name'], $rutaDestino);
    }

    // Actualizar datos en la BD
    $sqlUpdate = "UPDATE usuarios SET nombre=?, apellido=?, cedula=?, fecha_nacimiento=?, correo=?, telefono=?, fotografia=? WHERE id=?";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sssssssi", $nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $nombreFoto, $idUsuario);

    if ($stmtUpdate->execute()) {
        $_SESSION['usuario_nombre'] = $nombre; // actualiza nombre en la sesión
        echo "<script>
            alert('DATOS ACTUALIZADOS CORRECTAMENTE.');
            window.location.href='Actualizar_datosUsuarios.php';
        </script>";
    } else {
        echo "<script>alert('ERROR AL ACTUALIZAR LOS DATOS.');</script>";
    }
    $stmtUpdate->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Datos de Usuario</title>
    <link rel="stylesheet" href="../estilos/formulario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

<div class="form-container">
    <h2><i class="fa-solid fa-user-pen"></i> ACTUALIZAR DATOS DE USUARIO</h2>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label><i class="fa-solid fa-user"></i> Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-user"></i> Apellido:</label>
            <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-id-card"></i> Cédula:</label>
            <input type="text" name="cedula" value="<?= htmlspecialchars($usuario['cedula']) ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-calendar"></i> Fecha de nacimiento:</label>
            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-envelope"></i> Correo:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-phone"></i> Teléfono:</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-image"></i> Fotografía actual:</label><br>
            <?php if ($usuario['fotografia']): ?>
                <img src="../uploads/<?= $usuario['tipo'] === 'chofer' ? 'choferes' : 'pasajeros' ?>/<?= htmlspecialchars($usuario['fotografia']) ?>" width="100">
            <?php else: ?>
                <p>No hay imagen cargada.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-upload"></i> Nueva fotografía (opcional):</label>
            <input type="file" name="fotografia" accept="image/*">
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> ACTUALIZAR DATOS</button>
        <a href="<?= $_SESSION['usuario_tipo'] === 'chofer' ? 'chofer_dashboard.php' : 'dashboard_pasajero.php' ?>" class="btn-volver">
            <i class="fa-solid fa-arrow-left"></i> VOLVER AL DASHBOARD
        </a>

    </form>
</div>

</body>
</html>
