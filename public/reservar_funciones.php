<?php
require_once "../src/conexion.php";

/**
 * Crear una reserva (solo pasajeros)
 */
function crearReserva($conexion, $ride_id, $pasajero_id) {
    //Verificar si el pasajero ya tiene una reserva activa para este ride
    $stmt_check = $conexion->prepare(
        "SELECT COUNT(*) as total FROM reservas 
         WHERE id_ride = ? AND id_pasajero = ? AND estado IN ('Pendiente','Aceptada')"
    );
    $stmt_check->bind_param("ii", $ride_id, $pasajero_id);
    $stmt_check->execute();
    $total = $stmt_check->get_result()->fetch_assoc()['total'];
    if ($total > 0) {
        return ['exito' => false, 'mensaje' => "Ya tienes una reserva activa para este ride."];
    }

    //Verificar si hay espacios disponibles
    $stmt_espacios = $conexion->prepare("SELECT cantidad_espacios, chofer_id FROM rides WHERE id = ?");
    $stmt_espacios->bind_param("i", $ride_id);
    $stmt_espacios->execute();
    $ride_info = $stmt_espacios->get_result()->fetch_assoc();

    if (!$ride_info) {
        return ['exito' => false, 'mensaje' => "Ride no encontrado."];
    }

    $espacios = $ride_info['cantidad_espacios'];
    $chofer_id = $ride_info['chofer_id'];

    $stmt_reservadas = $conexion->prepare(
        "SELECT COUNT(*) as total FROM reservas WHERE id_ride = ? AND estado IN ('Pendiente','Aceptada')"
    );
    $stmt_reservadas->bind_param("i", $ride_id);
    $stmt_reservadas->execute();
    $reservadas = $stmt_reservadas->get_result()->fetch_assoc()['total'];

    if ($reservadas >= $espacios) {
        return ['exito' => false, 'mensaje' => "No hay espacios disponibles en este ride."];
    }

    //Insertar la reserva
    $stmt_insert = $conexion->prepare(
        "INSERT INTO reservas (id_ride, id_pasajero, id_chofer, estado) 
         VALUES (?, ?, ?, 'Pendiente')"
    );
    $stmt_insert->bind_param("iii", $ride_id, $pasajero_id, $chofer_id);

    if ($stmt_insert->execute()) {
        return ['exito' => true, 'mensaje' => "Reserva creada con éxito."];
    } else {
        return ['exito' => false, 'mensaje' => "Error al crear la reserva."];
    }
}

/**
 * Obtener reservas de un pasajero
 */
function obtenerReservasPasajero($conexion, $pasajero_id) {
    $sql = "SELECT r.id, r.estado, ri.nombre AS ride, ri.lugar_salida, ri.lugar_llegada, ri.dia, ri.hora
            FROM reservas r
            JOIN rides ri ON r.id_ride = ri.id
            WHERE r.id_pasajero = ?
            ORDER BY r.fecha_reserva DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $pasajero_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Obtener reservas de un chofer
 */
function obtenerReservasChofer($conexion, $chofer_id) {
    $sql = "SELECT r.id, r.estado, ri.nombre AS ride, u.nombre AS pasajero, ri.lugar_salida, ri.lugar_llegada, ri.dia, ri.hora
            FROM reservas r
            JOIN rides ri ON r.id_ride = ri.id
            JOIN usuarios u ON r.id_pasajero = u.id
            WHERE r.id_chofer = ?
            ORDER BY r.fecha_reserva DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $chofer_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Actualizar estado de reserva
 */
function actualizarEstadoReserva($conexion, $reserva_id, $nuevo_estado) {
    $stmt = $conexion->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $reserva_id);
    if ($stmt->execute()) {
        return ['exito'=>true, 'mensaje'=>"Estado actualizado a $nuevo_estado"];
    } else {
        return ['exito'=>false, 'mensaje'=>"Error al actualizar estado"];
    }
}

/**
 * Verificar si el usuario puede actuar sobre la reserva
 */
function puedeModificarReserva($conexion, $reserva_id, $usuario_id, $tipo) {
    if ($tipo === 'chofer') {
        $stmt = $conexion->prepare("SELECT estado FROM reservas WHERE id = ? AND id_chofer = ?");
    } else {
        $stmt = $conexion->prepare("SELECT estado FROM reservas WHERE id = ? AND id_pasajero = ?");
    }
    $stmt->bind_param("ii", $reserva_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) return ['exito'=>false, 'mensaje'=>'No tienes permiso', 'estado'=>null];
    return ['exito'=>true, 'estado'=>$result->fetch_assoc()['estado']];
}


