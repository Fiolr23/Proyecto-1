<?php
session_start();
require_once "conexion.php";
require_once "vehiculos.php";

// Verificar sesión y tipo de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

// Verificar que se recibió el id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: chofer_dashboard.php?mensaje=Vehículo+no+especificado");
    exit();
}

$vehiculo_id = intval($_GET['id']);
$chofer_id = $_SESSION['usuario_id'];

// Llamar a la función para eliminar el vehículo
if (eliminarVehiculo($conexion, $vehiculo_id, $chofer_id)) {
    header("Location: chofer_dashboard.php?mensaje=Vehículo+eliminado+correctamente");
    exit();
} else {
    header("Location: chofer_dashboard.php?mensaje=Error+al+eliminar+vehículo");
    exit();
}
