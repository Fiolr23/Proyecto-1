<?php
session_start();
require_once "../src/conexion.php";
require_once "rides.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ride_list.php");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];
$ride_id = intval($_GET['id']);

eliminarRide($conexion, $ride_id, $chofer_id);
header("Location: ride_list.php");
exit();
?>
