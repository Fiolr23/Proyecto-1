<?php
require_once "../src/conexion.php";

function obtenerVehiculosChofer($conexion, $chofer_id) {
    $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE chofer_id=?");
    $stmt->bind_param("i", $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehiculos = [];
    while ($row = $result->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    $stmt->close();
    return $vehiculos;
}

function obtenerVehiculo($conexion, $id, $chofer_id) {
    $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE id=? AND chofer_id=?");
    $stmt->bind_param("ii", $id, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehiculo = $result->fetch_assoc();
    $stmt->close();
    return $vehiculo;
}

function crearVehiculo($conexion, $chofer_id, $datos, $nombreFoto) {
    $sql = "INSERT INTO vehiculos (chofer_id, placa, color, marca, modelo, anio, capacidad_asientos, fotografia) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "issssiss",
        $chofer_id,
        $datos['placa'],
        $datos['color'],
        $datos['marca'],
        $datos['modelo'],
        $datos['anio'],
        $datos['capacidad_asientos'],
        $nombreFoto
    );
    $stmt->execute();
    $stmt->close();
}

function actualizarVehiculo($conexion, $id, $chofer_id, $datos, $nombreFoto = null) {
    if ($nombreFoto) {
        $sql = "UPDATE vehiculos SET placa=?, color=?, marca=?, modelo=?, anio=?, capacidad_asientos=?, fotografia=? WHERE id=? AND chofer_id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "sssssiiii",
            $datos['placa'],
            $datos['color'],
            $datos['marca'],
            $datos['modelo'],
            $datos['anio'],
            $datos['capacidad_asientos'],
            $nombreFoto,
            $id,
            $chofer_id
        );
    } else {
        $sql = "UPDATE vehiculos SET placa=?, color=?, marca=?, modelo=?, anio=?, capacidad_asientos=? WHERE id=? AND chofer_id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "ssssiiii",
            $datos['placa'],
            $datos['color'],
            $datos['marca'],
            $datos['modelo'],
            $datos['anio'],
            $datos['capacidad_asientos'],
            $id,
            $chofer_id
        );
    }
    $stmt->execute();
    $stmt->close();
}

function eliminarVehiculo($conexion, $id, $chofer_id) {
    $stmt = $conexion->prepare("DELETE FROM vehiculos WHERE id=? AND chofer_id=?");
    $stmt->bind_param("ii", $id, $chofer_id);
    $stmt->execute();
    $stmt->close();
}
?>
