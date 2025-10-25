<?php
require_once "../src/conexion.php";
require_once "../src/usuario.php";

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Validar si vienen los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = $_POST['contrasena'];
    $repetirContrasena = $_POST['repetir_contrasena'];
    $tipo = $_POST['tipo']; // pasajero o chofer

    // Validar contraseñas iguales
    if ($contrasena !== $repetirContrasena) {
        echo "<script>alert('❌ Las contraseñas no coinciden'); window.history.back();</script>";
        exit;
    }

    // Validar cédula y correo únicos
    $check = $conexion->prepare("SELECT id FROM usuarios WHERE cedula = ? OR correo = ?");
    $check->bind_param("ss", $cedula, $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('❌ Ya existe un usuario con esa cédula o correo'); window.history.back();</script>";
        $check->close();
        $conexion->close();
        exit;
    }
    $check->close();

    // Manejo de fotografía según tipo
    $carpetaDestino = $tipo === "pasajero" ? "../uploads/pasajeros/" : "../uploads/choferes/";
    if (!file_exists($carpetaDestino)) mkdir($carpetaDestino, 0777, true);

    $nombreFoto = null;
    if (!empty($_FILES['fotografia']['name'])) {
        $extension = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
        $nombreFoto = $cedula . "_" . time() . "." . $extension;
        $rutaDestino = $carpetaDestino . $nombreFoto;
        if (!move_uploaded_file($_FILES['fotografia']['tmp_name'], $rutaDestino)) {
            $nombreFoto = null;
        }
    }

    // Hashear contraseña
    $passwordHash = password_hash($contrasena, PASSWORD_BCRYPT);

    // Crear Token Activación
    $token = bin2hex(random_bytes(32));

    // Insertar usuario
    $sql = "INSERT INTO usuarios (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, contrasena, tipo, token_activacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssssss", $nombre, $apellido, $cedula, $fecha_nacimiento,
        $correo, $telefono, $nombreFoto, $passwordHash, $tipo, $token);

    if ($stmt->execute()) {
        // Enviar correo de activación
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'lazofiorella28@gmail.com'; // Cambia por tu correo
            $mail->Password = 'dbxf ejca elsr xeec'; // Contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('tucorreo@gmail.com', 'Sistema Rides');
            $mail->addAddress($correo, $nombre);
            $mail->isHTML(true);
            $mail->Subject = 'Activa tu cuenta';

            $url_activacion = "http://isw.utn.ac.cr/Proyecto1/public/activar_cuenta.php?token=$token";
            $mail->Body = "<h3>Hola $nombre:</h3>
                           <p>Gracias por registrarte. Activa tu cuenta haciendo clic aquí:</p>
                           <a href='$url_activacion'>Activar Cuenta</a>";

            $mail->send();

            echo "<script>alert('✅ Registro exitoso. Revisa tu correo para activar la cuenta'); 
                  window.location.href='../public/login.php';</script>";

        } catch (Exception $e) {
            echo "<script>alert('❌ Error al enviar el correo: {$mail->ErrorInfo}'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('❌ Error al registrar usuario'); window.history.back();</script>";
    }

    $stmt->close();
    $conexion->close();
}
?>




