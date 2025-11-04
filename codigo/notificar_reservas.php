<?php
// Script: notificar_reservas.php
// Uso: php notificar_reservas.php X minutos
// Envía correo a choferes si tienen reservas pendientes hace más de X minutos.

require_once __DIR__ . "/conexion.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Validar argumento (minutos)
if ($argc < 2) {
    echo "Uso: php notificar_reservas.php <minutos>\n";
    exit(1);
}

$minutos = intval($argv[1]);
echo "\nBuscando reservas pendientes con más de $minutos minutos...\n\n";

// Buscar reservas con más de X minutos sin aceptar ni rechazar
$sql = "SELECT r.id AS id_reserva, r.id_ride, r.id_chofer, r.id_pasajero, r.fecha_reserva,
               u.correo AS correo_chofer, u.nombre AS nombre_chofer
        FROM reservas r
        INNER JOIN usuarios u ON r.id_chofer = u.id
        WHERE r.estado = 'Pendiente'
        AND TIMESTAMPDIFF(MINUTE, r.fecha_reserva, NOW()) > ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $minutos);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No se encontraron reservas pendientes antiguas.\n";
    exit;
}

while ($row = $result->fetch_assoc()) {
    $correo = $row['correo_chofer'];
    $nombre = $row['nombre_chofer'];
    $id_reserva = $row['id_reserva'];
    $min = $minutos;

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lazofiorella28@gmail.com'; 
        $mail->Password = 'dbxf ejca elsr xeec';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('lazofiorella28@gmail.com', 'Sistema Rides');
        $mail->addAddress($correo, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Tienes solicitudes de reserva pendientes';
        $mail->Body = "<h3>Hola $nombre,</h3>
                       <p>Tienes solicitudes de reserva que llevan más de <b>$min</b> minutos sin responder.</p>
                       <p>Por favor ingresa al sistema y gestiona tus solicitudes pendientes.</p>
                       <p>Reserva ID: <b>$id_reserva</b></p>";

        $mail->send();
        echo "Notificación enviada a: $nombre <$correo>\n";

    } catch (Exception $e) {
        echo "Error al enviar correo a $correo: {$mail->ErrorInfo}\n";
    }
}

$stmt->close();
$conexion->close();

echo "\nProceso completado.\n";
?>
