<?php 
session_start();
require_once "../src/conexion.php";
require_once "vehiculos.php";
require_once "rides.php";

// Verificar sesión y tipo de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];

// Obtener vehículos y rides del chofer
$vehiculos = obtenerVehiculosChofer($conexion, $chofer_id);
if (!$vehiculos) $vehiculos = [];

$rides = obtenerRidesChofer($conexion, $chofer_id);
if (!$rides) $rides = [];

// Obtener reservas asociadas a los rides de este chofer
$sql = "SELECT re.id AS reserva_id, re.estado, u.nombre AS pasajero_nombre, u.apellido AS pasajero_apellido, 
               r.nombre AS ride_nombre, r.lugar_salida, r.lugar_llegada, r.dia, r.hora, r.hora_llegada
        FROM reservas re
        INNER JOIN rides r ON re.id_ride = r.id
        INNER JOIN usuarios u ON re.id_pasajero = u.id
        WHERE r.chofer_id = ?
        ORDER BY r.dia ASC, r.hora ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$reservas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Chofer</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<h2><i class="fa-solid fa-user-tie"></i> Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> </h2>

<!-- Vehículos -->
<h3><i class="fa-solid fa-car"></i> Vehículos</h3>
<a href="vehiculo_form.php" class="btn-agregar"><i class="fa-solid fa-circle-plus"></i> Agregar Vehículo</a>

<table class="tabla">
    <thead>
        <tr>
            <th><i class="fa-regular fa-image"></i> Foto</th>
            <th><i class="fa-solid fa-id-card"></i> Placa</th>
            <th><i class="fa-solid fa-industry"></i> Marca</th>
            <th><i class="fa-solid fa-car-side"></i> Modelo</th>
            <th><i class="fa-solid fa-palette"></i> Color</th>
            <th><i class="fa-solid fa-calendar"></i> Año</th>
            <th><i class="fa-solid fa-chair"></i> Asientos</th>
            <th><i class="fa-solid fa-gears"></i> Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($vehiculos as $v): ?>
        <tr>
            <td>
                <?php if(!empty($v['fotografia'])): ?>
                    <img src="../uploads/vehiculos/<?= htmlspecialchars($v['fotografia']) ?>" width="80">
                <?php else: ?>
                    <i class="fa-regular fa-image-slash"></i> N/A
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($v['placa']) ?></td>
            <td><?= htmlspecialchars($v['marca']) ?></td>
            <td><?= htmlspecialchars($v['modelo']) ?></td>
            <td><?= htmlspecialchars($v['color']) ?></td>
            <td><?= htmlspecialchars($v['anio']) ?></td>
            <td><?= htmlspecialchars($v['capacidad_asientos']) ?></td>
            <td>
                <a href="vehiculo_form.php?id=<?= $v['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a> |
                <a href="vehiculo_eliminar.php?id=<?= $v['id'] ?>" onclick="return confirm('¿Eliminar vehículo?');">
                    <i class="fa-solid fa-trash"></i> Eliminar
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Rides -->
<h3><i class="fa-solid fa-route"></i> Rides</h3>
<a href="ride_create.php" class="btn-agregar"><i class="fa-solid fa-circle-plus"></i> Agregar Ride</a>

<table class="tabla">
    <thead>
        <tr>
            <th><i class="fa-solid fa-signature"></i> Nombre</th>
            <th><i class="fa-solid fa-location-dot"></i> Salida</th>
            <th><i class="fa-solid fa-flag-checkered"></i> Llegada</th>
            <th><i class="fa-solid fa-calendar-day"></i> Día</th>
            <th><i class="fa-solid fa-clock"></i> Hora de Salida</th>
            <th><i class="fa-solid fa-clock-rotate-left"></i> Hora de Llegada</th>
            <th><i class="fa-solid fa-dollar-sign"></i> Costo</th>
            <th><i class="fa-solid fa-users"></i> Espacios</th>
            <th><i class="fa-solid fa-car"></i> Vehículo</th>
            <th><i class="fa-solid fa-gears"></i> Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rides as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['nombre']) ?></td>
            <td><?= htmlspecialchars($r['lugar_salida']) ?></td>
            <td><?= htmlspecialchars($r['lugar_llegada']) ?></td>
            <td><?= htmlspecialchars($r['dia']) ?></td>
            <td><?= htmlspecialchars($r['hora']) ?></td>
            <td><?= htmlspecialchars($r['hora_llegada']) ?></td>
            <td><?= htmlspecialchars($r['costo']) ?></td>
            <td><?= htmlspecialchars($r['cantidad_espacios']) ?></td>
            <td><?= htmlspecialchars($r['marca'] . " " . $r['modelo']) ?></td>
            <td>
                <a href="ride_edit.php?id=<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a> |
                <a href="ride_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar ride?');">
                    <i class="fa-solid fa-trash"></i> Eliminar
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Reservas -->
<h3><i class="fa-solid fa-book"></i> Reservas de mis Rides</h3>
<a href="buscar_rides.php" class="btn-agregar"><i class="fa-solid fa-magnifying-glass"></i> Buscar Rides</a>
<table class="tabla">
    <thead>
        <tr>
            <th><i class="fa-solid fa-user"></i> Pasajero</th>
            <th><i class="fa-solid fa-route"></i> Ride</th>
            <th><i class="fa-solid fa-location-dot"></i> Salida</th>
            <th><i class="fa-solid fa-flag-checkered"></i> Llegada</th>
            <th><i class="fa-solid fa-calendar-day"></i> Día</th>
            <th><i class="fa-solid fa-clock"></i> Hora</th>
            <th><i class="fa-solid fa-clock-rotate-left"></i> Hora Llegada</th>
            <th><i class="fa-solid fa-circle-info"></i> Estado</th>
            <th><i class="fa-solid fa-gears"></i> Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while($res = $reservas->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($res['pasajero_nombre'] . " " . $res['pasajero_apellido']) ?></td>
            <td><?= htmlspecialchars($res['ride_nombre']) ?></td>
            <td><?= htmlspecialchars($res['lugar_salida']) ?></td>
            <td><?= htmlspecialchars($res['lugar_llegada']) ?></td>
            <td><?= htmlspecialchars($res['dia']) ?></td>
            <td><?= htmlspecialchars($res['hora']) ?></td>
            <td><?= htmlspecialchars($res['hora_llegada']) ?></td>
            <td><?= htmlspecialchars($res['estado']) ?></td>
            <td>
                <?php if($res['estado'] === 'Pendiente'): ?>
                    <a href="reserva_chofer_accion.php?id=<?= $res['reserva_id'] ?>&accion=Aceptar">
                        <i class="fa-solid fa-check"></i> Aceptar
                    </a> | 
                    <a href="reserva_chofer_accion.php?id=<?= $res['reserva_id'] ?>&accion=Rechazar">
                        <i class="fa-solid fa-xmark"></i> Rechazar
                    </a>
                <?php elseif($res['estado'] === 'Aceptada'): ?>
                    <a href="reserva_chofer_accion.php?id=<?= $res['reserva_id'] ?>&accion=Cancelar">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </a>
                <?php else: ?>
                    <i class="fa-solid fa-minus"></i>
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
