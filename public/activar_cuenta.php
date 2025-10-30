<?php
require_once "../src/conexion.php";

if (!isset($_GET['token'])) {
    echo "<script>
            alert('Token no proporcionado.');
            window.location.href='login.php';
          </script>";
    exit;
}

$token = $_GET['token'];

$sql = "SELECT id FROM usuarios WHERE token_activacion = ? AND estado = 'Pendiente'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();
    $id = $usuario['id'];

    // Activar la cuenta
    $sqlUpdate = "UPDATE usuarios 
                  SET estado = 'Activo', token_activacion = NULL 
                  WHERE id = ?";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "<script>
            alert('Cuenta activada correctamente. ¡Ahora puedes iniciar sesión!');
            window.location.href='login.php';
          </script>";
} else {
    echo "<script>
            alert('Token inválido o la cuenta ya fue activada.');
            window.location.href='login.php';
          </script>";
}

$stmt->close();
$conexion->close();
?>

