<?php
session_start();
require_once "../src/conexion.php";

// Verificar sesión y tipo de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'pasajero') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$pasajero_id = $_SESSION['usuario_id'];

// Obtener reservas del pasajero
$sql = "SELECT re.id AS reserva_id, r.nombre AS ride_nombre, r.lugar_salida, r.lugar_llegada, 
               r.dia, r.hora, r.hora_llegada, r.costo, re.estado, v.marca, v.modelo, v.anio
        FROM reservas re
        INNER JOIN rides r ON re.id_ride = r.id
        INNER JOIN vehiculos v ON r.vehiculo_id = v.id
        WHERE re.id_pasajero = ?
        ORDER BY r.dia ASC, r.hora ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $pasajero_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pasajero</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Bienvenido, Panel de control Pasajero <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>

<!-- Buscar Rides -->
<p><a href="buscar_rides.php">🔍 Buscar Rides</a></p>

<!-- Reservas -->
<h3>Mis Reservas</h3>
<table class="tabla">
    <thead>
        <tr>
            <th>Ride</th>
            <th>Salida</th>
            <th>Llegada</th>
            <th>Día</th>
            <th>Hora</th>
            <th>Hora Llegada</th>
            <th>Costo</th>
            <th>Vehículo</th>
            <th>Año</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['ride_nombre']) ?></td>
            <td><?= htmlspecialchars($row['lugar_salida']) ?></td>
            <td><?= htmlspecialchars($row['lugar_llegada']) ?></td>
            <td><?= htmlspecialchars($row['dia']) ?></td>
            <td><?= htmlspecialchars($row['hora']) ?></td>
            <td><?= htmlspecialchars($row['hora_llegada']) ?></td>
            <td><?= htmlspecialchars($row['costo']) ?></td>
            <td><?= htmlspecialchars($row['marca'] . " " . $row['modelo']) ?></td>
            <td><?= htmlspecialchars($row['anio']) ?></td>
            <td><?= htmlspecialchars($row['estado']) ?></td>
            <td>
                <?php if(in_array($row['estado'], ['Pendiente', 'Aceptada'])): ?>
                    <a href="reserva_pasajero_accion.php?id=<?= $row['reserva_id'] ?>">Cancelar</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="cerrar_sesion.php">Cerrar sesión</a></p>
</body>
</html>

