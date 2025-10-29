<?php
session_start();
require_once "../src/conexion.php";
require_once "rides.php";
require_once "vehiculos.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'chofer') {
    header("Location: login.php?mensaje=Acceso+denegado");
    exit();
}

$chofer_id = $_SESSION['usuario_id'];

// Verificar que se envió el ID del ride
if (!isset($_GET['id'])) {
    header("Location: chofer_dashboard.php?mensaje=ID+no+especificado");
    exit();
}

$ride_id = intval($_GET['id']);

// Obtener el ride (verificando que pertenezca al chofer)
$ride = obtenerRidePorId($conexion, $ride_id, $chofer_id);
if (!$ride) {
    header("Location: chofer_dashboard.php?mensaje=Ride+no+encontrado");
    exit();
}

// IMPORTANTE: Obtener SOLO los vehículos del chofer actual
$vehiculos = obtenerVehiculosPorUsuario($conexion, $chofer_id);

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    
    // VALIDACIÓN CRÍTICA: Verificar que el vehículo pertenece al chofer
    $vehiculo_pertenece = false;
    foreach ($vehiculos as $v) {
        if ($v['id'] == $vehiculo_id) {
            $vehiculo_pertenece = true;
            break;
        }
    }
    
    if (!$vehiculo_pertenece) {
        $mensaje = "ERROR: El vehículo seleccionado no te pertenece";
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

        $ok = actualizarRide($conexion, $ride_id, $chofer_id, $data);
        if ($ok) {
            header("Location: chofer_dashboard.php?mensaje=Ride+actualizado+correctamente");
            exit();
        } else {
            $mensaje = "ERROR: " . ($GLOBALS['last_error'] ?? "No se pudo actualizar el ride");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar Ride</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Editar Ride</h2>

<?php if ($mensaje): ?>
    <p style="color:red;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<?php if (empty($vehiculos)): ?>
    <p style="color:red;">⚠️ No tienes vehículos registrados. <a href="vehiculo_form.php">Agrega uno primero</a>.</p>
<?php else: ?>

<form method="post">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($ride['nombre']) ?>" required><br>

    <label>Lugar de salida:</label>
    <input type="text" name="lugar_salida" value="<?= htmlspecialchars($ride['lugar_salida']) ?>" required><br>

    <label>Lugar de llegada:</label>
    <input type="text" name="lugar_llegada" value="<?= htmlspecialchars($ride['lugar_llegada']) ?>" required><br>

    <label>Día:</label>
    <input type="date" name="dia" value="<?= htmlspecialchars($ride['dia']) ?>" required><br>

    <label>Hora de salida:</label>
    <input type="time" name="hora" value="<?= htmlspecialchars($ride['hora']) ?>" required><br>

    <label>Hora de llegada:</label>
    <input type="time" name="hora_llegada" value="<?= htmlspecialchars($ride['hora_llegada']) ?>" required><br>

    <label>Costo:</label>
    <input type="number" step="0.01" name="costo" value="<?= htmlspecialchars($ride['costo']) ?>" required><br>

    <label>Cantidad de espacios:</label>
    <input type="number" name="cantidad_espacios" value="<?= htmlspecialchars($ride['cantidad_espacios']) ?>" required><br>

    <label>Vehículo:</label>
    <select name="vehiculo_id" required>
        <option value="">-- Seleccione un vehículo --</option>
        <?php foreach($vehiculos as $v): ?>
            <option value="<?= $v['id'] ?>" <?= ($v['id'] == $ride['vehiculo_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ")") ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <button type="submit">Actualizar Ride</button>
</form>

<?php endif; ?>

<p><a href="chofer_dashboard.php">Volver al Dashboard</a></p>
</body>
</html>