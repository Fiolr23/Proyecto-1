<?php 
session_start();
require_once "../src/conexion.php";
require_once "vehiculos.php";
require_once "rides.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];
$vehiculos = obtenerVehiculosChofer($conexion, $chofer_id);
$rides = obtenerRidesChofer($conexion, $chofer_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Chofer</title>
    <!-- Font Awesome CDN para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>

<h3>Vehículos</h3>
<a href="vehiculo_form.php"><i class="fa-solid fa-plus"></i> Agregar Vehículo</a>
<ul>
<?php foreach($vehiculos as $v): ?>
    <li>
        <?= htmlspecialchars($v['marca']." ".$v['modelo']." (".$v['placa'].")") ?>
        - <a href="vehiculo_form.php?id=<?= $v['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
        - <a href="vehiculo_eliminar.php?id=<?= $v['id'] ?>"><i class="fa-solid fa-trash"></i> Eliminar</a>
    </li>
<?php endforeach; ?>
</ul>

<h3>Rides</h3>
<a href="ride_form.php"><i class="fa-solid fa-plus"></i> Agregar Ride</a>
<ul>
<?php foreach($rides as $r): ?>
    <li>
        <?= htmlspecialchars($r['nombre']." de ".$r['lugar_salida']." a ".$r['lugar_llegada']." el ".$r['dia']." a ".$r['hora']." (Vehículo: ".$r['marca']." ".$r['modelo'].")") ?>
        - <a href="ride_form.php?id=<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
        - <a href="ride_eliminar.php?id=<?= $r['id'] ?>"><i class="fa-solid fa-trash"></i> Eliminar</a>
    </li>
<?php endforeach; ?>
</ul>

<p><a href="cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a></p>
</body>
</html>

