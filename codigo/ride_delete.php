<?php
session_start();
require_once "conexion.php";
require_once "rides.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('ID de ride no especificado.'); window.location.href='ride_list.php';</script>";
    exit();
}

$chofer_id = $_SESSION['usuario_id'];
$ride_id = intval($_GET['id']);

$resultado = eliminarRide($conexion, $ride_id, $chofer_id);

if ($resultado === true) {
    echo "<script>alert('Ride eliminado correctamente.'); window.location.href='ride_list.php';</script>";
} elseif ($resultado === 'No se puede eliminar el ride porque tiene reservas activas.') {
    echo "<script>alert('No se puede eliminar el ride porque tiene reservas activas.'); window.location.href='ride_list.php';</script>";
} else {
    echo "<script>alert('Error: $resultado'); window.location.href='ride_list.php';</script>";
}
?>

