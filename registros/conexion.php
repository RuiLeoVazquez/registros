<?php
$conexion = new mysqli("localhost", "root", "", "sae_escolar");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");
?>
