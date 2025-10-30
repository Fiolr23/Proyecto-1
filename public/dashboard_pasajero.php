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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

<h2>
    <i class="fa-solid fa-user"></i> 
    Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> 
    <small>(Panel de Pasajero)</small>
</h2>

<!-- Buscar Rides -->
<p>
    <a href="buscar_rides.php" class="btn-accion">
        <i class="fa-solid fa-magnifying-glass"></i> Buscar Rides
    </a>
</p>

<!-- Reservas -->
<h3><i class="fa-solid fa-car-side"></i> Mis Reservas</h3>
<table class="tabla">
    <thead>
        <tr>
            <th><i class="fa-solid fa-route"></i> Ride</th>
            <th><i class="fa-solid fa-location-dot"></i> Salida</th>
            <th><i class="fa-solid fa-flag-checkered"></i> Llegada</th>
            <th><i class="fa-solid fa-calendar-day"></i> Día</th>
            <th><i class="fa-solid fa-clock"></i> Hora</th>
            <th><i class="fa-solid fa-hourglass-end"></i> Hora Llegada</th>
            <th><i class="fa-solid fa-dollar-sign"></i> Costo</th>
            <th><i class="fa-solid fa-car"></i> Vehículo</th>
            <th><i class="fa-solid fa-calendar"></i> Año</th>
            <th><i class="fa-solid fa-info-circle"></i> Estado</th>
            <th><i class="fa-solid fa-gears"></i> Acción</th>
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
                    <a href="reserva_pasajero_accion.php?id=<?= $row['reserva_id'] ?>&accion=Cancelar" 
                       onclick="return confirm('¿Seguro que deseas cancelar esta reserva?');" 
                       class="btn-accion">
                       <i class="fa-solid fa-ban"></i> Cancelar
                    </a>
                <?php else: ?>
                    <span class="text-muted"><i class="fa-solid fa-minus"></i></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p>
    <a href="cerrar_sesion.php" class="btn-salir">
        <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
    </a>
</p>

</body>
</html>



