<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    $sql = "SELECT id, nombre, apellido, contrasena, tipo, estado FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $nombre, $apellido, $hash, $tipo, $estado);
        $stmt->fetch();

        // Validar estado
        if ($estado === "Pendiente") {
            $_SESSION['login_error'] = "Tu cuenta está pendiente de activación. Revisa tu correo.";
            header("Location: /Proyecto1/codigo/login.php");
            exit();
        } elseif ($estado === "Inactivo") {
            $_SESSION['login_error'] = "Tu cuenta está inactiva. Contacta con un administrador.";
            header("Location: /Proyecto1/codigo/login.php");
            exit();
        }

        // Verificar contraseña
        
        if (password_verify($contrasena, $hash)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_tipo'] = $tipo;

            // Redireccionar según tipo de usuario
            switch ($tipo) {
                case "administrador":
                    header("Location: /Proyecto1/codigo/admin_dashboard.php");
                    break;
                case "chofer":
                    header("Location: /Proyecto1/codigo/chofer_dashboard.php");
                    break;
                case "pasajero":
                    header("Location: /Proyecto1/codigo/dashboard_pasajero.php");
                    break;
                default:
                    $_SESSION['login_error'] = "Tipo de usuario no reconocido.";
                    header("Location: /Proyecto1/codigo/login.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Contraseña incorrecta.";
            header("Location: /Proyecto1/codigo/login.php");
            exit();
        }

    } else {
        $_SESSION['login_error'] = "No existe un usuario con ese correo.";
        header("Location: /Proyecto1/codigo/login.php");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>



