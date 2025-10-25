<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>

    <?php if(isset($_GET['mensaje'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['mensaje']) ?></p>
    <?php endif; ?>

    <h2>Registro</h2>

    <form action="procesar_registro.php" method="POST" enctype="multipart/form-data">

        <label>Nombre:</label>
        <input type="text" name="nombre" required><br><br>

        <label>Apellido:</label>
        <input type="text" name="apellido" required><br><br>

        <label>Cédula:</label>
        <input type="text" name="cedula" required 
               pattern="[0-9]{9,12}" 
               title="Solo números (9 a 12 dígitos)"
               maxlength="12"
               oninput="this.value = this.value.replace(/[^0-9]/g, '');"><br><br>

        <label>Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required><br><br>

        <label>Correo:</label>
        <input type="email" name="correo" required><br><br>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required 
               pattern="[0-9]{8}" 
               title="Debe contener 8 dígitos numéricos"
               maxlength="8"
               oninput="this.value = this.value.replace(/[^0-9]/g, '');"><br><br>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required><br><br>

        <label>Repetir contraseña:</label>
        <input type="password" name="repetir_contrasena" required><br><br>

        <label>Tipo de usuario:</label>
        <select name="tipo" required>
            <option value="pasajero">Pasajero</option>
            <option value="chofer">Chofer</option>
        </select>
        <br><br>

        <label>Fotografía (opcional):</label>
        <input type="file" name="fotografia" accept="image/*"><br><br>

        <button type="submit">Registrarse</button>
    </form>

    <br>
    <a href="login.php"><button type="button">Regresar al Login</button></a>

</body>
</html>
