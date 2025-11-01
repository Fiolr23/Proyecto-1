<?php
include 'conexion.php';
$id_vehiculo = $_GET['id'];

// Obtener datos del vehículo
$result = $conexion->query("SELECT * FROM vehiculos WHERE id_vehiculo = '$id_vehiculo'");
$vehiculo = $result->fetch_assoc();

if (!$vehiculo) {
    echo "Vehículo no encontrado";
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'];
    $color = $_POST['color'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $anio = $_POST['anio'];
    $capacidad = $_POST['capacidad'];

    $foto = $vehiculo['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $foto = "uploads/" . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
    }

    $conexion->query("UPDATE vehiculos SET 
        placa='$placa', color='$color', marca='$marca', modelo='$modelo', anio='$anio', capacidad='$capacidad', foto='$foto' 
        WHERE id_vehiculo='$id_vehiculo'");

    header("Location: vehiculos.php?mensaje=Vehículo actualizado correctamente");
}
?>

<h2>Editar Vehículo</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="placa" value="<?php echo $vehiculo['placa']; ?>" required>
    <input type="text" name="color" value="<?php echo $vehiculo['color']; ?>" required>
    <input type="text" name="marca" value="<?php echo $vehiculo['marca']; ?>" required>
    <input type="text" name="modelo" value="<?php echo $vehiculo['modelo']; ?>" required>
    <input type="number" name="anio" value="<?php echo $vehiculo['anio']; ?>" required>
    <input type="number" name="capacidad" value="<?php echo $vehiculo['capacidad']; ?>" required>
    <input type="file" name="foto" accept="image/*">
    <?php if ($vehiculo['foto']): ?>
        <img src="<?php echo $vehiculo['foto']; ?>" width="100">
    <?php endif; ?>
    <button type="submit">Actualizar Vehículo</button>
</form>
<a href="vehiculos.php">Volver a la lista</a>
