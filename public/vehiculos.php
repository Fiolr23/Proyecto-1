<?php
require_once "../src/conexion.php";

/**
 * Obtener todos los vehículos de un chofer específico
 */
function obtenerVehiculosChofer($conexion, $chofer_id) {
    $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE chofer_id=? ORDER BY marca, modelo");
    if (!$stmt) return [];
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

/**
 * Obtener UN vehículo específico SOLO si pertenece al chofer
 */
function obtenerVehiculo($conexion, $id, $chofer_id) {
    $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE id=? AND chofer_id=?");
    if (!$stmt) return null;
    $stmt->bind_param("ii", $id, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehiculo = $result->fetch_assoc();
    $stmt->close();
    return $vehiculo;
}

/**
 * ✅ VALIDAR QUE UN VEHÍCULO PERTENECE AL CHOFER (por ID)
 */
function validarPropiedadVehiculo($conexion, $vehiculo_id, $chofer_id) {
    $stmt = $conexion->prepare("SELECT id FROM vehiculos WHERE id=? AND chofer_id=?");
    if (!$stmt) return false;
    $stmt->bind_param("ii", $vehiculo_id, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existe = $result->num_rows > 0;
    $stmt->close();
    return $existe;
}

/**
 * ✅ VALIDAR QUE UN VEHÍCULO PERTENECE AL CHOFER (por PLACA)
 */
function validarPropiedadVehiculoPorPlaca($conexion, $placa, $chofer_id) {
    $stmt = $conexion->prepare("SELECT id FROM vehiculos WHERE placa=? AND chofer_id=?");
    if (!$stmt) return false;
    $stmt->bind_param("si", $placa, $chofer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existe = $result->num_rows > 0;
    $stmt->close();
    return $existe;
}

/**
 * Crear vehículo (solo para el chofer actual)
 */
function crearVehiculo($conexion, $chofer_id, $datos, $nombreFoto) {
    // Validar placa única por chofer
    $stmtCheck = $conexion->prepare("SELECT id FROM vehiculos WHERE placa = ? AND chofer_id = ?");
    $stmtCheck->bind_param("si", $datos['placa'], $chofer_id);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        echo "<script>alert('❌ Ya tienes un vehículo con esa placa'); window.history.back();</script>";
        exit();
    }
    $stmtCheck->close();

    // Insertar vehículo
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

/**
 * Obtener vehículos SOLO del usuario especificado
 */
function obtenerVehiculosPorUsuario($conexion, $usuario_id) {
    $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE chofer_id=? ORDER BY marca, modelo");
    if (!$stmt) return [];
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehiculos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $vehiculos;
}

/**
 * Actualizar vehículo (verificando propiedad)
 */
function actualizarVehiculo($conexion, $id, $chofer_id, $datos, $nombreFoto = null) {
    // ✅ VALIDAR QUE EL VEHÍCULO PERTENECE AL CHOFER
    if (!validarPropiedadVehiculo($conexion, $id, $chofer_id)) {
        echo "<script>alert('❌ Este vehículo no te pertenece'); window.history.back();</script>";
        exit();
    }

    // Validar que no exista otra placa igual para este chofer
    $stmtCheck = $conexion->prepare("SELECT id FROM vehiculos WHERE placa = ? AND chofer_id = ? AND id != ?");
    $stmtCheck->bind_param("sii", $datos['placa'], $chofer_id, $id);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        echo "<script>alert('❌ Ya tienes otro vehículo con esa placa'); window.history.back();</script>";
        exit();
    }
    $stmtCheck->close();

    if ($nombreFoto) {
        $sql = "UPDATE vehiculos SET placa=?, color=?, marca=?, modelo=?, anio=?, capacidad_asientos=?, fotografia=? 
                WHERE id=? AND chofer_id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "ssssissii",
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
        $sql = "UPDATE vehiculos SET placa=?, color=?, marca=?, modelo=?, anio=?, capacidad_asientos=? 
                WHERE id=? AND chofer_id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "sssisiii",
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

/**
 * Eliminar vehículo (verificando propiedad)
 */
function eliminarVehiculo($conexion, $vehiculo_id, $chofer_id) {
    // ✅ VERIFICAR QUE EL VEHÍCULO PERTENECE AL CHOFER
    $stmt = $conexion->prepare("SELECT id, fotografia FROM vehiculos WHERE id = ? AND chofer_id = ?");
    $stmt->bind_param("ii", $vehiculo_id, $chofer_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 0) {
        $stmt->close();
        return false; // No pertenece al chofer
    }

    $vehiculo = $resultado->fetch_assoc();
    $stmt->close();
    
    // Eliminar fotografía si existe
    if (!empty($vehiculo['fotografia'])) {
        $ruta = "../uploads/vehiculos/" . $vehiculo['fotografia'];
        if (file_exists($ruta)) unlink($ruta);
    }

    // Eliminar vehículo de la base de datos
    $stmt = $conexion->prepare("DELETE FROM vehiculos WHERE id = ? AND chofer_id = ?");
    $stmt->bind_param("ii", $vehiculo_id, $chofer_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

?>