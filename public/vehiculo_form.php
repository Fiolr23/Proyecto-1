<?php
session_start();
require_once "../src/conexion.php";
require_once "vehiculos.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];

// Inicializar variables
$vehiculo = [
    'placa' => '',
    'color' => '',
    'marca' => '',
    'modelo' => '',
    'anio' => '',
    'capacidad_asientos' => ''
];
$nombreFoto = null;
$editar = false;

// Si viene id, cargamos datos del vehículo
if (isset($_GET['id'])) {
    $editar = true;
    $vehiculo_id = intval($_GET['id']);
    $vehiculoDB = obtenerVehiculo($conexion, $vehiculo_id, $chofer_id);
    if ($vehiculoDB) {
        $vehiculo = $vehiculoDB;
    } else {
        echo "<script>alert('Vehículo no encontrado'); window.location.href='chofer_dashboard.php';</script>";
        exit();
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'placa' => trim($_POST['placa']),
        'color' => trim($_POST['color']),
        'marca' => trim($_POST['marca']),
        'modelo' => trim($_POST['modelo']),
        'anio' => intval($_POST['anio']),
        'capacidad_asientos' => intval($_POST['capacidad_asientos'])
    ];

    // Manejo de foto
    if (!empty($_FILES['fotografia']['name'])) {
        $extension = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
        $nombreFoto = $datos['placa'] . "_" . time() . "." . $extension;
        $carpetaDestino = "../uploads/vehiculos/";
        if (!file_exists($carpetaDestino)) mkdir($carpetaDestino, 0777, true);
        move_uploaded_file($_FILES['fotografia']['tmp_name'], $carpetaDestino . $nombreFoto);
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Editar
        actualizarVehiculo($conexion, $_POST['id'], $chofer_id, $datos, $nombreFoto);
        echo "<script>alert('Vehículo actualizado correctamente'); window.location.href='chofer_dashboard.php';</script>";
    } else {
        // Crear
        crearVehiculo($conexion, $chofer_id, $datos, $nombreFoto);
        echo "<script>alert('Vehículo agregado correctamente'); window.location.href='chofer_dashboard.php';</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $editar ? "Editar Vehículo" : "Agregar Vehículo" ?></title>
</head>
<body>
<h2><?= $editar ? "Editar Vehículo" : "Agregar Vehículo" ?></h2>

<form action="" method="POST" enctype="multipart/form-data">
    <?php if ($editar): ?>
        <input type="hidden" name="id" value="<?= $vehiculo_id ?>">
    <?php endif; ?>

    <label>Placa:</label>
    <input type="text" name="placa" value="<?= htmlspecialchars($vehiculo['placa']) ?>" required><br><br>

    <label>Color:</label>
    <input type="text" name="color" value="<?= htmlspecialchars($vehiculo['color']) ?>" required><br><br>

    <label>Marca:</label>
    <input type="text" name="marca" value="<?= htmlspecialchars($vehiculo['marca']) ?>" required><br><br>

    <label>Modelo:</label>
    <input type="text" name="modelo" value="<?= htmlspecialchars($vehiculo['modelo']) ?>" required><br><br>

    <label>Año:</label>
    <input type="number" name="anio" value="<?= htmlspecialchars($vehiculo['anio']) ?>" required><br><br>

    <label>Capacidad de Asientos:</label>
    <input type="number" name="capacidad_asientos" value="<?= htmlspecialchars($vehiculo['capacidad_asientos']) ?>" required><br><br>

    <label>Fotografía (opcional):</label>
    <input type="file" name="fotografia" accept="image/*"><br><br>

    <?php if ($editar && $vehiculo['fotografia']): ?>
        <img src="../uploads/vehiculos/<?= htmlspecialchars($vehiculo['fotografia']) ?>" width="150"><br><br>
    <?php endif; ?>

    <button type="submit"><?= $editar ? "Actualizar" : "Agregar" ?></button>
</form>

<br>
<a href="chofer_dashboard.php"><button>Volver al Inicio</button></a>

</body>
</html>
