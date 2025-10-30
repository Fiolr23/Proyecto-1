<?php
session_start();
require_once "../src/conexion.php";

// Variables de búsqueda
$origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';

// --- NUEVO BLOQUE DE ORDENAMIENTO ---
$ordenar_por = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'r.dia';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';

$columnas_validas = ['r.dia', 'r.lugar_salida', 'r.lugar_llegada'];
if (!in_array($ordenar_por, $columnas_validas)) $ordenar_por = 'r.dia';
$orden = ($orden === 'DESC') ? 'DESC' : 'ASC';
// --- FIN NUEVO BLOQUE ---

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

// 🔽 Aplicar ordenamiento
$sql .= " ORDER BY $ordenar_por $orden, CONCAT(r.dia,' ',r.hora) ASC";

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
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

<?php if (isset($_GET['msg'])): ?>
    <p class="mensaje"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<div class="contenedor">
    <h2><i class="fas fa-car-side"></i> Buscar Rides Disponibles</h2>

    <!--Formulario de búsqueda -->
    <form method="GET" class="form-busqueda">
        <input type="text" name="origen" placeholder="Ubicación de salida" value="<?= htmlspecialchars($origen) ?>">
        <input type="text" name="destino" placeholder="Ubicación de llegada" value="<?= htmlspecialchars($destino) ?>">
        <button type="submit" class="btn-buscar">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>

    <!--NUEVO: Formulario de ordenamiento -->
    <form method="GET" class="form-orden" style="margin-bottom: 1em;">
        <!-- Preservar filtros de búsqueda -->
        <input type="hidden" name="origen" value="<?= htmlspecialchars($origen) ?>">
        <input type="hidden" name="destino" value="<?= htmlspecialchars($destino) ?>">

        <label for="ordenar_por"><i class="fas fa-sort"></i> Ordenar por:</label>
        <select name="ordenar_por" id="ordenar_por">
            <option value="r.dia" <?= $ordenar_por === 'r.dia' ? 'selected' : '' ?>>Fecha</option>
            <option value="r.lugar_salida" <?= $ordenar_por === 'r.lugar_salida' ? 'selected' : '' ?>>Lugar de origen</option>
            <option value="r.lugar_llegada" <?= $ordenar_por === 'r.lugar_llegada' ? 'selected' : '' ?>>Lugar de destino</option>
        </select>

        <select name="orden" id="orden">
            <option value="ASC" <?= $orden === 'ASC' ? 'selected' : '' ?>>Ascendente ↑</option>
            <option value="DESC" <?= $orden === 'DESC' ? 'selected' : '' ?>>Descendente ↓</option>
        </select>

        <button type="submit" class="btn-ordenar">
            <i class="fas fa-arrow-down-a-z"></i> Aplicar
        </button>
    </form>
    <!--FIN NUEVO -->

    <table class="tabla">
        <thead>
            <tr>
                <th><i class="fas fa-map-marker-alt"></i> Salida</th>
                <th><i class="fas fa-map-pin"></i> Llegada</th>
                <th><i class="fas fa-calendar-day"></i> Día</th>
                <th><i class="fas fa-clock"></i> Hora</th>
                <th><i class="fas fa-hourglass-end"></i> Hora Llegada</th>
                <th><i class="fas fa-car"></i> Vehículo</th>
                <th><i class="fas fa-calendar"></i> Año</th>
                <th><i class="fas fa-chair"></i> Espacios</th>
                <th><i class="fas fa-cogs"></i> Acción</th>
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
                        <span><i class="fas fa-sign-in-alt"></i> Inicia sesión</span>

                    <?php elseif($_SESSION['usuario_tipo'] === 'chofer'): ?>
                        <span><i class="fas fa-user-shield"></i> No disponible</span>

                    <?php elseif($reserva_usuario): ?>
                        <span class="no-reservar"><i class="fas fa-check-circle"></i> Ya reservado</span>

                    <?php elseif($espacios_disponibles <= 0): ?>
                        <span class="no-reservar"><i class="fas fa-times-circle"></i> Ocupado</span>

                    <?php else: ?>
                        <a href="reservar_ride_form.php?id=<?= $row['id'] ?>" class="btn-reservar">
                            <i class="fas fa-ticket-alt"></i> Reservar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

    <a href="<?= $dashboard ?>" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>
</body>
</html>



