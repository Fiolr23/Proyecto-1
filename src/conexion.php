<?php
$host = "localhost";
$usuario = "fiolazo25";
$contrasena = "FiOrElLa25";
$base_datos = "rides_bd";

$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}
?>

