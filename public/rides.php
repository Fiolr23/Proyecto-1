<?php
require_once "../src/conexion.php";

function obtenerRidesChofer($conexion, $chofer_id) {
    $stmt = $conexion->prepare("SELECT r.*, v.marca, v.modelo, v.placa FROM rides r 
                                LEFT JOIN vehiculos v ON r.vehiculo_id=v.id 
                                WHERE r.chofer_id=? ORDER BY r.dia, r.hora");
    $stmt->bind_param("i", $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rides = [];
    while ($row = $result->fetch_assoc()) {
        $rides[] = $row;
    }
    $stmt->close();
    return $rides;
}

function obtenerRide($conexion, $id, $chofer_id) {
    $stmt = $conexion->prepare("SELECT * FROM rides WHERE id=? AND chofer_id=?");
    $stmt->bind_param("ii", $id, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();
    $stmt->close();
    return $ride;
}

function crearRide($conexion, $chofer_id, $datos) {
    $sql = "INSERT INTO rides (chofer_id, vehiculo_id, nombre, lugar_salida, lugar_llegada, dia, hora, costo, cantidad_espacios) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "iisssssii",
        $chofer_id,
        $datos['vehiculo_id'],
        $datos['nombre'],
        $datos['lugar_salida'],
        $datos['lugar_llegada'],
        $datos['dia'],
        $datos['hora'],
        $datos['costo'],
        $datos['cantidad_espacios']
    );
    $stmt->execute();
    $stmt->close();
}

function actualizarRide($conexion, $id, $chofer_id, $datos) {
    $sql = "UPDATE rides SET vehiculo_id=?, nombre=?, lugar_salida=?, lugar_llegada=?, dia=?, hora=?, costo=?, cantidad_espacios=? 
            WHERE id=? AND chofer_id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "isssssiiii",
        $datos['vehiculo_id'],
        $datos['nombre'],
        $datos['lugar_salida'],
        $datos['lugar_llegada'],
        $datos['dia'],
        $datos['hora'],
        $datos['costo'],
        $datos['cantidad_espacios'],
        $id,
        $chofer_id
    );
    $stmt->execute();
    $stmt->close();
}

function eliminarRide($conexion, $id, $chofer_id) {
    $stmt = $conexion->prepare("DELETE FROM rides WHERE id=? AND chofer_id=?");
    $stmt->bind_param("ii", $id, $chofer_id);
    $stmt->execute();
    $stmt->close();
}
?>
