<?php
session_start();
require_once "../src/conexion.php";
require_once "reservar_funciones.php";

//Validar que el usuario sea pasajero
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'pasajero') {
    header("Location: login.php?mensaje=" . urlencode("Acceso denegado"));
    exit();
}

$pasajero_id = $_SESSION['usuario_id'];
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

//Validar parámetros básicos
if (!$reserva_id || $accion !== 'Cancelar') {
    header("Location: dashboard_pasajero.php?mensaje=" . urlencode("Acción inválida"));
    exit();
}

//Verificar si el pasajero puede modificar la reserva
$estado_info = puedeModificarReserva($conexion, $reserva_id, $pasajero_id, 'pasajero');

if (!$estado_info['exito']) {
    header("Location: dashboard_pasajero.php?mensaje=" . urlencode("No puedes modificar esta reserva"));
    exit();
}

$estado_actual = $estado_info['estado'];

// 🚦 Lógica de transición de estados
if (in_array($estado_actual, ['Pendiente', 'Aceptada'])) {
    // Puede cancelar si está pendiente o aceptada
    $resultado = actualizarEstadoReserva($conexion, $reserva_id, 'Cancelada');
} else {
    // No puede cancelar si ya fue rechazada o cancelada
    $resultado = ['exito' => false, 'mensaje' => "No puedes cancelar una reserva en estado '$estado_actual'"];
}

//Redirigir con mensaje de resultado
if ($resultado['exito']) {
    header("Location: dashboard_pasajero.php?mensaje=" . urlencode($resultado['mensaje']));
} else {
    header("Location: dashboard_pasajero.php?mensaje=" . urlencode($resultado['mensaje']));
}
exit();
?>

