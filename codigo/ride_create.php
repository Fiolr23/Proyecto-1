<?php
session_start();
require_once "conexion.php";
require_once "rides.php";
require_once "vehiculos.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];

//FILTRAR SOLO LOS VEHÍCULOS DEL CHOFER ACTUAL
$vehiculos = obtenerVehiculosPorUsuario($conexion, $chofer_id);

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    
    //VALIDACIÓN DE SEGURIDAD: Verificar que el vehículo pertenece al chofer
    $vehiculo_pertenece = false;
    foreach ($vehiculos as $v) {
        if ($v['id'] == $vehiculo_id) {
            $vehiculo_pertenece = true;
            break;
        }
    }
    
    if (!$vehiculo_pertenece) {
        $mensaje = "ERROR: El vehículo seleccionado no te pertenece o no existe";
    } else {
        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'lugar_salida' => trim($_POST['lugar_salida'] ?? ''),
            'lugar_llegada' => trim($_POST['lugar_llegada'] ?? ''),
            'dia' => $_POST['dia'] ?? '',
            'hora' => $_POST['hora'] ?? '',
            'hora_llegada' => $_POST['hora_llegada'] ?? '',
            'costo' => floatval($_POST['costo'] ?? 0),
            'cantidad_espacios' => intval($_POST['cantidad_espacios'] ?? 0),
            'vehiculo_id' => $vehiculo_id
        ];

        $ok = crearRide($conexion, $chofer_id, $data);
        if ($ok) {
            header("Location: chofer_dashboard.php?mensaje=Ride+creado+correctamente");
            exit();
        } else {
            $mensaje = "ERROR: " . ($GLOBALS['last_error'] ?? "No se pudo crear el ride");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Crear Ride</title>
<link rel="stylesheet" href="../estilos/style.css">
<link rel="stylesheet" href="../estilos/formulario.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="form-container">
    <h2><i class="fa-solid fa-car"></i> Crear Ride</h2>

    <?php if ($mensaje): ?>
        <p class="mensaje error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if (empty($vehiculos)): ?>
        <p class="mensaje error">
            No tienes vehículos registrados. 
            <a href="vehiculo_form.php" class="btn-accion">
                <i class="fa-solid fa-car-side"></i> Agregar vehículo
            </a>
        </p>
        <p><a href="chofer_dashboard.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Dashboard</a></p>
    <?php else: ?>

    <form method="post" class="registro-form">
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label>Lugar de salida:</label>
            <input type="text" name="lugar_salida" required>
        </div>

        <div class="form-group">
            <label>Lugar de llegada:</label>
            <input type="text" name="lugar_llegada" required>
        </div>

        <div class="form-group">
            <label>Día:</label>
            <input type="date" name="dia" required>
        </div>

        <div class="form-group">
            <label>Hora de salida:</label>
            <input type="time" name="hora" required>
        </div>

        <div class="form-group">
            <label>Hora de llegada:</label>
            <input type="time" name="hora_llegada" required>
        </div>

        <div class="form-group">
            <label>Costo:</label>
            <input type="number" step="0.01" name="costo" required>
        </div>

        <div class="form-group">
            <label>Cantidad de espacios:</label>
            <input type="number" name="cantidad_espacios" required>
        </div>

        <div class="form-group">
            <label>Vehículo:</label>
            <select name="vehiculo_id" required>
                <option value="">-- Seleccione un vehículo --</option>
                <?php foreach($vehiculos as $v): ?>
                    <option value="<?= $v['id'] ?>">
                        <?= htmlspecialchars($v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ")") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Crear Ride</button>
            <a href="chofer_dashboard.php" class="btn-volver">
                <i class="fa-solid fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </form>

    <?php endif; ?>
</div>

</body>
</html>
