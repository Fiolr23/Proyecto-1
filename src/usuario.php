<?php 
class Usuario { 

    public $nombre;
    public $apellido;
    public $cedula;
    public $fecha_nacimiento;
    public $correo;
    public $telefono;
    public $fotografia;
    public $contrasena;
    public $tipo;
    public $estado;
    public $token;

    public function __construct($nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $fotografia, $contrasena, $tipo = "pasajero", $estado = "Pendiente", $token = null) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->cedula = $cedula;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->fotografia = $fotografia;
        $this->contrasena = $contrasena;
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->token = $token;
    }
}
?>

