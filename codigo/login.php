<?php 
session_start();

// Mostrar mensaje de error si existe
$mensaje = "";
if (isset($_SESSION['login_error'])) {
    $mensaje = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Transporte</title>
    <link rel="stylesheet" href="../estilos/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<form action="procesar_login.php" method="POST" class="login-form">
    <h2><i class="fa-solid fa-right-to-bracket"></i> Inicio de Sesión</h2>

    <?php if ($mensaje != ""): ?>
        <p class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="form-group">
        <label for="correo"><i class="fa-solid fa-envelope"></i> Correo:</label>
        <input type="email" name="correo" required>
    </div>

    <div class="form-group">
        <label for="contrasena"><i class="fa-solid fa-lock"></i> Contraseña:</label>
        <input type="password" name="contrasena" required>
    </div>

    <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Ingresar</button>

    <p class="text-muted">¿No tienes cuenta?</p>

    <a href="registro.php" class="btn btn-login"><i class="fa-solid fa-user-plus"></i> Crear cuenta</a>
</form>

</body>
</html>









