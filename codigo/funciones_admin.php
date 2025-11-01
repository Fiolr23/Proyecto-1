<?php
require_once "conexion.php";

function obtenerUsuarios($conexion) {
    $sql = "SELECT id, nombre, apellido, correo, tipo, estado FROM usuarios ORDER BY tipo, nombre";
    return $conexion->query($sql);
}

function desactivarUsuario($conexion, $id) {
    $sql = "UPDATE usuarios SET estado = 'Inactivo' WHERE id = ? AND estado != 'Inactivo'";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function crearAdmin($conexion, $nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena) {
    // Validar que no exista otro usuario con el mismo correo o cédula
    $check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? OR cedula = ?");
    $check->bind_param("ss", $correo, $cedula);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        return "Ya existe un usuario con ese correo o cédula.";
    }
    $check->close();

    $hash = password_hash($contrasena, PASSWORD_BCRYPT);
    $sql = "INSERT INTO usuarios (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, contrasena, tipo, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'administrador', 'Activo')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssss", $nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $hash);
    if ($stmt->execute()) {
        return true;
    } else {
        return "Error al crear el administrador.";
    }
}
?>
