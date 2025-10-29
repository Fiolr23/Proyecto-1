<?php
session_start();
require_once "../src/conexion.php";
require_once "reservar_funciones.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

if (!$reserva_id || !in_array($accion, ['Aceptar','Rechazar','Cancelar'])) {
    header("Location: chofer_dashboard.php");
    exit();
}

// Verificar si puede modificar la reserva
$estado_actual = puedeModificarReserva($conexion, $reserva_id, $chofer_id, 'chofer');
if (!$estado_actual) {
    header("Location: chofer_dashboard.php?mensaje=No+puedes+modificar+esta+reserva");
    exit();
}

// Validar lógica según estado
if ($estado_actual == 'Pendiente' && in_array($accion, ['Aceptar', 'Rechazar'])) {
    // Convertir acción a estado válido para la BD
    $nuevo_estado = ($accion == 'Aceptar') ? 'Aceptada' : 'Rechazada';
    actualizarEstadoReserva($conexion, $reserva_id, $nuevo_estado);
}elseif ($estado_actual == 'Aceptada' && $accion == 'Cancelar') {
    actualizarEstadoReserva($conexion, $reserva_id, 'Cancelada');
}



header("Location: chofer_dashboard.php");
exit();


