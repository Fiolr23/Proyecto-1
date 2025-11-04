<?php
session_start();
require_once "conexion.php";
require_once "reservar_funciones.php";

// Verificar que el usuario sea pasajero
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'pasajero') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

// Verificar que se haya recibido el id del ride
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?mensaje=Ride+no+seleccionado");
    exit();
}

$ride_id = intval($_GET['id']);

// Obtener datos del ride
$sql = "SELECT r.id, r.nombre, r.lugar_salida, r.lugar_llegada, r.dia, r.hora, r.hora_llegada, 
               r.costo, r.cantidad_espacios, v.marca, v.modelo, v.anio
        FROM rides r
        INNER JOIN vehiculos v ON r.vehiculo_id = v.id
        WHERE r.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $ride_id);
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();

if (!$ride) {
    die("Ride no encontrado");
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pasajero_id = $_SESSION['usuario_id'];
    $resultado = crearReserva($conexion, $ride_id, $pasajero_id);

    if ($resultado['exito']) {
        header("Location: index.php?mensaje=" . urlencode($resultado['mensaje']));
        exit();
    } else {
        $error = $resultado['mensaje'];
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar Ride</title>
    <link rel="stylesheet" href="../estilos/style.css">
</head>
<body>
<div class="contenedor">
    <h2>Reservar Ride</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <p><strong>Nombre del Ride:</strong> <?= htmlspecialchars($ride['nombre']) ?></p>
        <p><strong>Salida:</strong> <?= htmlspecialchars($ride['lugar_salida']) ?></p>
        <p><strong>Llegada:</strong> <?= htmlspecialchars($ride['lugar_llegada']) ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($ride['dia']) ?></p>
        <p><strong>Hora salida:</strong> <?= htmlspecialchars($ride['hora']) ?></p>
        <p><strong>Hora llegada:</strong> <?= htmlspecialchars($ride['hora_llegada']) ?></p>
        <p><strong>Vehículo:</strong> <?= htmlspecialchars($ride['marca'] . " " . $ride['modelo'] . " " . $ride['anio']) ?></p>
        <p><strong>Espacios disponibles:</strong> <?= htmlspecialchars($ride['cantidad_espacios']) ?></p>
        <p><strong>Costo:</strong> $<?= htmlspecialchars($ride['costo']) ?></p>

        <button type="submit" class="btn-reservar">Confirmar Reserva</button>
    </form>

    <p><a href="index.php">Volver a Buscar Rides</a></p>
    
</div>
</body>
</html>
