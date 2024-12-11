<?php
header('Content-Type: application/json');
try {
    // Reutiliza la conexión y las consultas anteriores
    $data = [
        "totalUsuarios" => $totalUsuarios,
        "totalEstudiantes" => $totalEstudiantes
    ];
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
