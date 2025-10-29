<?php
session_start();
require_once "../src/conexion.php";
require_once "reservar_funciones.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'pasajero') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$pasajero_id = $_SESSION['usuario_id'];
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$reserva_id) {
    header("Location: dashboard_pasajero.php");
    exit();
}

// Verificar estado y propiedad de la reserva
$estado_actual = puedeModificarReserva($conexion, $reserva_id, $pasajero_id, 'pasajero');
if (!$estado_actual) {
    header("Location: dashboard_pasajero.php?mensaje=No+puedes+modificar+esta+reserva");
    exit();
}

if (in_array($estado_actual, ['Pendiente', 'Aceptada'])) {
    actualizarEstadoReserva($conexion, $reserva_id, 'Cancelada');
}

header("Location: dashboard_pasajero.php");
exit();
