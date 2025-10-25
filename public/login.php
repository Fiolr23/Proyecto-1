<?php 
session_start();

// Mostrar mensaje de error si existe
$mensaje = "";
if (isset($_SESSION['login_error'])) {
    $mensaje = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Limpiar mensaje para no repetirlo
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Transporte</title>
</head>
<body>

<h2>Inicio de Sesión</h2>

<?php if ($mensaje != ""): ?>
    <p style="color:red;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<form action="procesar_login.php" method="POST">
    <label for="correo">Correo:</label><br>
    <input type="email" name="correo" required><br><br>

    <label for="contrasena">Contraseña:</label><br>
    <input type="password" name="contrasena" required><br><br>

    <button type="submit">Ingresar</button>
</form>

<br>

<form action="registro.php">
    <button type="submit">Crear cuenta</button>
</form>

</body>
</html>





