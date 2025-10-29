<?php
session_start();
require_once "../src/conexion.php";

// Variables de búsqueda y ordenamiento
$origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
$ordenar_por = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'r.dia';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';

$columnas_validas = ['r.dia', 'r.lugar_salida', 'r.lugar_llegada'];
if (!in_array($ordenar_por, $columnas_validas)) $ordenar_por = 'r.dia';
$orden = ($orden === 'DESC') ? 'DESC' : 'ASC';

// Consulta rides con conteo de reservas activas
$sql = "SELECT r.id, r.nombre, r.lugar_salida, r.lugar_llegada, r.dia, r.hora, r.hora_llegada,
               r.cantidad_espacios, v.marca, v.modelo, v.anio,
               (SELECT COUNT(*) FROM reservas 
                WHERE id_ride = r.id AND estado IN ('Pendiente','Aceptada')) AS reservados
        FROM rides r
        INNER JOIN vehiculos v ON r.vehiculo_id = v.id
        WHERE CONCAT(r.dia, ' ', r.hora) >= NOW()";

$params = [];
$tipos = "";
if ($origen !== '') {
    $sql .= " AND r.lugar_salida LIKE ?";
    $params[] = "%$origen%";
    $tipos .= "s";
}
if ($destino !== '') {
    $sql .= " AND r.lugar_llegada LIKE ?";
    $params[] = "%$destino%";
    $tipos .= "s";
}

$sql .= " ORDER BY CONCAT(r.dia,' ',r.hora) ASC, $ordenar_por $orden";

$stmt = $conexion->prepare($sql);
if (!empty($params)) $stmt->bind_param($tipos, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$dashboard = "dashboard.php";
if (isset($_SESSION['usuario_tipo'])) {
    if ($_SESSION['usuario_tipo'] === 'chofer') $dashboard = "chofer_dashboard.php";
    if ($_SESSION['usuario_tipo'] === 'pasajero') $dashboard = "dashboard_pasajero.php";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda de Rides</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (isset($_GET['msg'])): ?>
    <p class="mensaje"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<div class="contenedor">
    <h2>Buscar Rides Disponibles</h2>

    <form method="GET" class="form-busqueda">
        <input type="text" name="origen" placeholder="Ubicación de salida" value="<?= htmlspecialchars($origen) ?>">
        <input type="text" name="destino" placeholder="Ubicación de llegada" value="<?= htmlspecialchars($destino) ?>">
        <button type="submit" class="btn-buscar">Buscar</button>
    </form>

    <table class="tabla">
        <thead>
            <tr>
                <th>Salida</th>
                <th>Llegada</th>
                <th>Día</th>
                <th>Hora</th>
                <th>Hora Llegada</th>
                <th>Vehículo</th>
                <th>Año</th>
                <th>Espacios</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>

        <?php while($row = $result->fetch_assoc()): 
            
            $espacios_disponibles = $row['cantidad_espacios'] - $row['reservados'];
            $reserva_usuario = false;

            if (isset($_SESSION['id'])) {
                $id_usuario = $_SESSION['id'];

                $sql2 = "SELECT estado FROM reservas 
                        WHERE id_ride = ? AND id_pasajero = ? 
                        AND estado IN ('Pendiente','Aceptada')";
                $stmt2 = $conexion->prepare($sql2);
                $stmt2->bind_param("ii", $row['id'], $id_usuario);
                $stmt2->execute();
                $reserva_usuario = $stmt2->get_result()->num_rows > 0;
            }
        ?>

            <tr>
                <td><?= htmlspecialchars($row['lugar_salida']) ?></td>
                <td><?= htmlspecialchars($row['lugar_llegada']) ?></td>
                <td><?= htmlspecialchars($row['dia']) ?></td>
                <td><?= htmlspecialchars($row['hora']) ?></td>
                <td><?= htmlspecialchars($row['hora_llegada']) ?></td>
                <td><?= htmlspecialchars($row['marca']." ".$row['modelo']) ?></td>
                <td><?= htmlspecialchars($row['anio']) ?></td>
                <td><?= $espacios_disponibles ?></td>
                <td>

                <?php if(!isset($_SESSION['usuario_tipo'])): ?>
                    <span>Inicia sesión</span>

                <?php elseif($_SESSION['usuario_tipo'] === 'chofer'): ?>
                    <span>No disponible</span>

                <?php elseif($reserva_usuario): ?>
                    <span class="no-reservar">Ya reservado ✅</span>

                <?php elseif($espacios_disponibles <= 0): ?>
                    <span class="no-reservar">Ocupado ❌</span>

                <?php else: ?>
                    <a href="reservar_ride_form.php?id=<?= $row['id'] ?>" class="btn-reservar">Reservar</a>
                <?php endif; ?>

                </td>
            </tr>

        <?php endwhile; ?>

        </tbody>
    </table>

    <a href="<?= $dashboard ?>" class="btn-volver">← Volver</a>

</div>
</body>
</html>


