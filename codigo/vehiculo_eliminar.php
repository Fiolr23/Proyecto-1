<?php
require_once "conexion.php";
require_once "vehiculos.php";

session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

// Verificar ID del vehículo
if (!isset($_GET['id'])) {
    echo "<script>alert('ID de vehículo no especificado.'); window.location.href='chofer_dashboard.php';</script>";
    exit;
}

$idVehiculo = intval($_GET['id']);
$idUsuario = $_SESSION['usuario_id'];


$resultado = eliminarVehiculo($conexion, $idVehiculo, $idUsuario);

if ($resultado === true) {
    echo "<script>alert('Vehículo eliminado correctamente.'); window.location.href='chofer_dashboard.php';</script>";
} else {

    echo "<script>alert('$resultado'); window.location.href='chofer_dashboard.php';</script>";
}
?>



