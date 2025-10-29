<?php
session_start();
require_once "../src/conexion.php";
require_once "rides.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];
$rides = obtenerRidesChofer($conexion, $chofer_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Rides</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Mis Rides</h2>
<a href="ride_create.php"><button>Agregar Ride</button></a><br><br>
<table>
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
            <td><?= htmlspecialchars($r['marca']." ".$r['modelo']." (".$r['placa'].")") ?></td>
            <td>
                <a href="ride_edit.php?id=<?= $r['id'] ?>">Editar</a> |
                <a href="ride_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar este ride?');">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
<a href="chofer_dashboard.php"><button>Volver al Inicio</button></a>
</html>
