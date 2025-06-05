<?php
include '../../php/docente_session.php';
include '../../php/conexion_be.php';

$query = "SELECT nombre, direccion, latitud, longitud FROM edificios";
$resultado = $conexion->query($query);

$datos = [];

while ($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode($datos);