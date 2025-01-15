<?php
require_once '../../php/conexion_be.php';

header('Content-Type: application/json');
if (isset($_GET['query'])) {
    $conexion = mysqli_connect("localhost", "root", "", "login_register_db");
    $query = mysqli_real_escape_string($conexion, $_GET['query']);
    $sql = "SELECT id, nombre_completo FROM estudiantes WHERE nombre_completo LIKE '%$query%' LIMIT 10";
    $result = mysqli_query($conexion, $sql);

    if ($result) {
        $estudiantes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $estudiantes[] = [
                'id' => $row['id'],
                'nombre_completo' => htmlspecialchars($row['nombre_completo'])
            ];
        }
        echo json_encode($estudiantes);
    } else {
        echo json_encode(['error' => 'Error en la consulta SQL']);
    }
    exit;
}