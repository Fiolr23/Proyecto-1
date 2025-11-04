<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/vehiculos.php";

/**
 * Último error de función
 * @var string
 */
$GLOBALS['last_error'] = '';

//crearRide
function crearRide($conexion, $chofer_id, $datos) {
    $GLOBALS['last_error'] = '';

    // VALIDAR QUE EL VEHÍCULO PERTENECE AL CHOFER
    if (!validarPropiedadVehiculo($conexion, $datos['vehiculo_id'], $chofer_id)) {
        $GLOBALS['last_error'] = "El vehículo seleccionado no te pertenece";
        return false;
    }

    
    $stmtVehiculo = $conexion->prepare("SELECT capacidad_asientos FROM vehiculos WHERE id = ? AND chofer_id = ?");
    if (!$stmtVehiculo) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }
    $stmtVehiculo->bind_param("ii", $datos['vehiculo_id'], $chofer_id);
    $stmtVehiculo->execute();
    $stmtVehiculo->bind_result($capacidad_asientos);
    $stmtVehiculo->fetch();
    $stmtVehiculo->close();

    if (!$capacidad_asientos) {
        $GLOBALS['last_error'] = "Vehículo no encontrado o no tienes acceso a él";
        return false;
    }

    // Calcular espacios disponibles (asientos - 1 por el chofer)
    $espacios_maximos = max(0, intval($capacidad_asientos) - 1);

    if (intval($datos['cantidad_espacios']) > $espacios_maximos) {
        $GLOBALS['last_error'] = "Cantidad de espacios ({$datos['cantidad_espacios']}) mayor a los disponibles ({$espacios_maximos})";
        return false;
    }

    // Normalizar nombres de campos de hora
    $hora_salida_raw = isset($datos['hora']) ? $datos['hora'] : (isset($datos['hora_salida']) ? $datos['hora_salida'] : null);
    $hora_llegada_raw = isset($datos['hora_llegada']) ? $datos['hora_llegada'] : null;

    if (empty($hora_salida_raw) || empty($hora_llegada_raw)) {
        $GLOBALS['last_error'] = "Debe indicar hora de salida y hora de llegada";
        return false;
    }

    // Asegurar formato correcto HH:MM:SS
    $hora_salida = date("H:i:s", strtotime($hora_salida_raw));
    $hora_llegada = date("H:i:s", strtotime($hora_llegada_raw));

    if (!$hora_salida || !$hora_llegada) {
        $GLOBALS['last_error'] = "Formato de hora inválido";
        return false;
    }


    if (strtotime($hora_llegada) <= strtotime($hora_salida)) {
        $GLOBALS['last_error'] = "La hora de llegada debe ser posterior a la hora de salida";
        return false;
    }


    $stmtCheck = $conexion->prepare("
        SELECT id FROM rides
        WHERE vehiculo_id=? AND dia=? AND chofer_id=?
        AND NOT (hora_llegada <= ? OR hora >= ?)
    ");
    if (!$stmtCheck) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }
    $stmtCheck->bind_param("issss", $datos['vehiculo_id'], $datos['dia'], $chofer_id, $hora_salida, $hora_llegada);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        $GLOBALS['last_error'] = "Ya existe un ride que se solapa en ese vehículo en ese horario";
        return false;
    }
    $stmtCheck->close();

    // Insertar ride con validación de propiedad
    $stmt = $conexion->prepare("
        INSERT INTO rides 
        (chofer_id, vehiculo_id, nombre, lugar_salida, lugar_llegada, dia, hora, hora_llegada, costo, cantidad_espacios)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }

    $stmt->bind_param(
        "iissssssdi",
        $chofer_id,
        $datos['vehiculo_id'],
        $datos['nombre'],
        $datos['lugar_salida'],
        $datos['lugar_llegada'],
        $datos['dia'],
        $hora_salida,
        $hora_llegada,
        $datos['costo'],
        $datos['cantidad_espacios']
    );

    if (!$stmt->execute()) {
        $GLOBALS['last_error'] = $stmt->error;
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

// obtenerRidesChofer 
function obtenerRidesChofer($conexion, $chofer_id) {
    // SOLO OBTENER RIDES DEL CHOFER (con JOIN para validar vehículos)
    $stmt = $conexion->prepare("
        SELECT r.id, r.nombre, r.lugar_salida, r.lugar_llegada, r.dia, 
               r.hora, r.hora_llegada, r.costo, r.cantidad_espacios,
               v.marca, v.modelo, v.placa, v.id as vehiculo_id
        FROM rides r
        JOIN vehiculos v ON r.vehiculo_id = v.id AND v.chofer_id = ?
        WHERE r.chofer_id = ?
        ORDER BY r.dia, r.hora
    ");
    if (!$stmt) {
        $GLOBALS['last_error'] = $conexion->error;
        return [];
    }
    $stmt->bind_param("ii", $chofer_id, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rides = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rides;
}

/// obtenerRidePorId 
function obtenerRidePorId($conexion, $ride_id, $chofer_id) {
    //VALIDAR QUE EL RIDE Y EL VEHÍCULO PERTENECEN AL CHOFER
    $stmt = $conexion->prepare("
        SELECT r.* 
        FROM rides r
        JOIN vehiculos v ON r.vehiculo_id = v.id AND v.chofer_id = ?
        WHERE r.id=? AND r.chofer_id=?
    ");
    if (!$stmt) {
        $GLOBALS['last_error'] = $conexion->error;
        return null;
    }
    $stmt->bind_param("iii", $chofer_id, $ride_id, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();
    $stmt->close();
    return $ride;
}

//actualizarRide
function actualizarRide($conexion, $ride_id, $chofer_id, $datos) {
    $GLOBALS['last_error'] = '';

    //VALIDAR QUE EL RIDE PERTENECE AL CHOFER
    $stmtRide = $conexion->prepare("SELECT id FROM rides WHERE id=? AND chofer_id=?");
    if (!$stmtRide) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }
    $stmtRide->bind_param("ii", $ride_id, $chofer_id);
    $stmtRide->execute();
    $stmtRide->store_result();
    if ($stmtRide->num_rows === 0) {
        $stmtRide->close();
        $GLOBALS['last_error'] = "Este ride no te pertenece";
        return false;
    }
    $stmtRide->close();

    //NUEVA VALIDACIÓN: Revisar si hay reservas activas o pendientes
    $sql = "SELECT COUNT(*) AS total
            FROM reservas
            WHERE id_ride = ?
            AND estado IN ('Pendiente', 'Aceptada')";
    $stmtCheckReservas = $conexion->prepare($sql);
    if (!$stmtCheckReservas) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }
    $stmtCheckReservas->bind_param("i", $ride_id);
    $stmtCheckReservas->execute();
    $res = $stmtCheckReservas->get_result()->fetch_assoc();
    $stmtCheckReservas->close();

    if ($res['total'] > 0) {
        $GLOBALS['last_error'] = "No se puede actualizar el ride porque tiene reservas activas o pendientes.";
        return false;
    }

    //VALIDAR QUE EL NUEVO VEHÍCULO PERTENECE AL CHOFER
    if (!validarPropiedadVehiculo($conexion, $datos['vehiculo_id'], $chofer_id)) {
        $GLOBALS['last_error'] = "El vehículo seleccionado no te pertenece";
        return false;
    }

    $hora_salida_raw = isset($datos['hora']) ? $datos['hora'] : (isset($datos['hora_salida']) ? $datos['hora_salida'] : null);
    $hora_llegada_raw = isset($datos['hora_llegada']) ? $datos['hora_llegada'] : null;

    if (empty($hora_salida_raw) || empty($hora_llegada_raw)) {
        $GLOBALS['last_error'] = "Debe indicar hora de salida y hora de llegada";
        return false;
    }

    $hora_salida = date("H:i:s", strtotime($hora_salida_raw));
    $hora_llegada = date("H:i:s", strtotime($hora_llegada_raw));

    if (!$hora_salida || !$hora_llegada) {
        $GLOBALS['last_error'] = "Formato de hora inválido";
        return false;
    }

    if (strtotime($hora_llegada) <= strtotime($hora_salida)) {
        $GLOBALS['last_error'] = "La hora de llegada debe ser posterior a la hora de salida";
        return false;
    }


    $stmtCheck = $conexion->prepare("
        SELECT id FROM rides 
        WHERE vehiculo_id=? AND dia=? AND id != ? AND chofer_id=?
        AND NOT (hora_llegada <= ? OR hora >= ?)
    ");
    if (!$stmtCheck) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }
    $stmtCheck->bind_param("ississ", $datos['vehiculo_id'], $datos['dia'], $ride_id, $chofer_id, $hora_salida, $hora_llegada);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        $GLOBALS['last_error'] = "Ya existe un ride que se solapa en ese vehículo en ese horario";
        return false;
    }
    $stmtCheck->close();

    // Verificar capacidad (con validación de propiedad)
    $stmtVehiculo = $conexion->prepare("SELECT capacidad_asientos FROM vehiculos WHERE id = ? AND chofer_id = ?");
    if (!$stmtVehiculo) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }
    $stmtVehiculo->bind_param("ii", $datos['vehiculo_id'], $chofer_id);
    $stmtVehiculo->execute();
    $stmtVehiculo->bind_result($capacidad_asientos);
    $stmtVehiculo->fetch();
    $stmtVehiculo->close();

    $espacios_maximos = max(0, intval($capacidad_asientos) - 1);
    if (intval($datos['cantidad_espacios']) > $espacios_maximos) {
        $GLOBALS['last_error'] = "Cantidad de espacios mayor a los disponibles ({$espacios_maximos})";
        return false;
    }

    // Ejecutar UPDATE (con validación de chofer)
    $stmt = $conexion->prepare("
        UPDATE rides 
        SET vehiculo_id=?, nombre=?, lugar_salida=?, lugar_llegada=?, dia=?, hora=?, hora_llegada=?, costo=?, cantidad_espacios=? 
        WHERE id=? AND chofer_id=?
    ");
    if (!$stmt) {
        $GLOBALS['last_error'] = $conexion->error;
        return false;
    }

    $stmt->bind_param(
        "issssssdiii",
        $datos['vehiculo_id'],
        $datos['nombre'],
        $datos['lugar_salida'],
        $datos['lugar_llegada'],
        $datos['dia'],
        $hora_salida,
        $hora_llegada,
        $datos['costo'],
        $datos['cantidad_espacios'],
        $ride_id,
        $chofer_id
    );

    if (!$stmt->execute()) {
        $GLOBALS['last_error'] = $stmt->error;
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

// eliminarRide 
function eliminarRide($conexion, $ride_id, $chofer_id) { 
    // 1️ VALIDAR QUE EL RIDE Y EL VEHÍCULO PERTENECEN AL CHOFER
    $stmtValidar = $conexion->prepare("
        SELECT r.id 
        FROM rides r
        JOIN vehiculos v ON r.vehiculo_id = v.id
        WHERE r.id = ? AND r.chofer_id = ? AND v.chofer_id = ?
    ");
    if (!$stmtValidar) {
        $GLOBALS['last_error'] = $conexion->error;
        return "Error en validación.";
    }

    $stmtValidar->bind_param("iii", $ride_id, $chofer_id, $chofer_id);
    $stmtValidar->execute();
    $stmtValidar->store_result();

    if ($stmtValidar->num_rows === 0) {
        $stmtValidar->close();
        return "No autorizado o ride inexistente.";
    }
    $stmtValidar->close();

    // 2️ VERIFICAR SI HAY RESERVAS ACTIVAS (Pendiente o Aceptada)
    $sql = "SELECT COUNT(*) AS total 
            FROM reservas 
            WHERE id_ride = ? 
            AND estado IN ('Pendiente', 'Aceptada')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res['total'] > 0) {
        return "No se puede eliminar el ride porque tiene reservas activas.";
    }

    // 3️ ELIMINAR RESERVAS ANTIGUAS (Rechazada o Cancelada)
    $sql = "DELETE FROM reservas WHERE id_ride = ? AND estado IN ('Rechazada', 'Cancelada')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $stmt->close();

    // 4️ ELIMINAR EL RIDE
    $stmt = $conexion->prepare("DELETE FROM rides WHERE id = ? AND chofer_id = ?");
    $stmt->bind_param("ii", $ride_id, $chofer_id);
    $resultado = $stmt->execute();
    $stmt->close();

    return $resultado ? true : "Error al eliminar el ride.";
}

?>