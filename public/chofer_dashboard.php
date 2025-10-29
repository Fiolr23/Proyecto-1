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
if (!$vehiculos) $vehiculos = []; // Inicializar array si no hay vehículos

$rides = obtenerRidesChofer($conexion, $chofer_id);
if (!$rides) $rides = []; // Inicializar array si no hay rides

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
    <!-- Font Awesome CDN para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<h2>Bienvenido, Panel de control Chofer <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>

<!-- Vehículos -->
<h3>Vehículos</h3>
<a href="vehiculo_form.php"><i class="fa-solid fa-plus"></i> Agregar Vehículo</a>
<table class="tabla">
    <thead>
        <tr>
            <th>Foto</th>
            <th>Placa</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Color</th>
            <th>Año</th>
            <th>Asientos</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($vehiculos as $v): ?>
        <tr>
            <td>
                <?php if(!empty($v['fotografia'])): ?>
                    <img src="../uploads/vehiculos/<?= htmlspecialchars($v['fotografia']) ?>" width="80">
                <?php else: ?>
                    N/A
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
                <a href="vehiculo_eliminar.php?id=<?= $v['id'] ?>" onclick="return confirm('¿Eliminar vehículo?');"><i class="fa-solid fa-trash"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Rides -->
<h3>Rides</h3>
<a href="ride_create.php"><i class="fa-solid fa-plus"></i> Agregar Ride</a>
<table class="tabla">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Salida</th>
            <th>Llegada</th>
            <th>Día</th>
            <th>Hora de Salida</th>
            <th>Hora de Llegada</th>
            <th>Costo</th>
            <th>Espacios</th>
            <th>Vehículo</th>
            <th>Acciones</th>
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
                <a href="ride_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar ride?');"><i class="fa-solid fa-trash"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<!-- Reservas de chofer -->
<h3>Reservas de mis Rides</h3>
<table class="tabla">
    <thead>
        <tr>
            <th>Pasajero</th>
            <th>Ride</th>
            <th>Salida</th>
            <th>Llegada</th>
            <th>Día</th>
            <th>Hora</th>
            <th>Hora Llegada</th>
            <th>Estado</th>
            <th>Acciones</th>
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
                    <a href="reserva_chofer_accion.php?id=<?= $res['reserva_id'] ?>&accion=Aceptar">Aceptar</a> | 
                    <a href="reserva_chofer_accion.php?id=<?= $res['reserva_id'] ?>&accion=Rechazar">Rechazar</a>
                <?php elseif($res['estado'] === 'Aceptada'): ?>
                    <a href="reserva_chofer_accion.php?id=<?= $res['reserva_id'] ?>&accion=Cancelar">Cancelar</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a></p>
</body>
</html>