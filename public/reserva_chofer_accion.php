<?php
session_start();
require_once "../src/conexion.php";
require_once "reservar_funciones.php";

// Validar que el usuario sea un chofer
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=" . urlencode("Acceso denegado"));
    exit();
}

$chofer_id = $_SESSION['usuario_id'];
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

//Validar parámetros básicos
if (!$reserva_id || !in_array($accion, ['Aceptar', 'Rechazar', 'Cancelar'])) {
    header("Location: chofer_dashboard.php?mensaje=" . urlencode("Acción inválida"));
    exit();
}

// Verificar si el chofer puede modificar la reserva
$estado_info = puedeModificarReserva($conexion, $reserva_id, $chofer_id, 'chofer');

if (!$estado_info['exito']) {
    header("Location: chofer_dashboard.php?mensaje=" . urlencode("No puedes modificar esta reserva"));
    exit();
}

$estado_actual = $estado_info['estado'];

//Lógica de transición de estados
if ($estado_actual === 'Pendiente' && in_array($accion, ['Aceptar', 'Rechazar'])) {
    $nuevo_estado = ($accion === 'Aceptar') ? 'Aceptada' : 'Rechazada';
    $resultado = actualizarEstadoReserva($conexion, $reserva_id, $nuevo_estado);
} elseif ($estado_actual === 'Aceptada' && $accion === 'Cancelar') {
    $resultado = actualizarEstadoReserva($conexion, $reserva_id, 'Cancelada');
} else {
    $resultado = ['exito' => false, 'mensaje' => "Acción no permitida para el estado actual"];
}

//Redirigir con mensaje de resultado
if ($resultado['exito']) {
    header("Location: chofer_dashboard.php?mensaje=" . urlencode($resultado['mensaje']));
} else {
    header("Location: chofer_dashboard.php?mensaje=" . urlencode($resultado['mensaje']));
}
exit();
?>



