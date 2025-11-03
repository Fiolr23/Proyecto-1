<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <!-- Estilos generales -->
    <link rel="stylesheet" href="../estilos/style.css">
    <link rel="stylesheet" href="../estilos/formulario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <div class="form-container">
        <h2><i class="fa-solid fa-user-plus"></i> Registro</h2>

        <?php if(isset($_GET['mensaje'])): ?>
            <p class="mensaje error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['mensaje']) ?></p>
        <?php endif; ?>

        <form action="procesar_registro.php" method="POST" enctype="multipart/form-data" class="registro-form">

            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="form-group">
                <label>Apellido:</label>
                <input type="text" name="apellido" required>
            </div>

            <div class="form-group">
                <label>Cédula:</label>
                <input type="text" name="cedula" required 
                       pattern="[0-9]{9,12}" 
                       title="Solo números (9 a 12 dígitos)"
                       maxlength="12"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <div class="form-group">
                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento" required>
            </div>

            <div class="form-group">
                <label>Correo:</label>
                <input type="email" name="correo" required>
            </div>

            <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" name="telefono" required 
                       pattern="[0-9]{8}" 
                       title="Debe contener 8 dígitos numéricos"
                       maxlength="8"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="contrasena" required>
            </div>

            <div class="form-group">
                <label>Repetir contraseña:</label>
                <input type="password" name="repetir_contrasena" required>
            </div>

            <div class="form-group">
                <label>Tipo de usuario:</label>
                <select name="tipo" required>
                    <option value="pasajero">Pasajero</option>
                    <option value="chofer">Chofer</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fotografía (opcional):</label>
                <input type="file" name="fotografia" accept="image/*">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">
                    <i class="fa-solid fa-user-plus"></i> Registrarse
                </button>

                <a href="login.php" class="btn-volver">
                    <i class="fa-solid fa-arrow-left"></i> Regresar al Login
                </a>
            </div>

        </form>
    </div>

</body>
</html>



