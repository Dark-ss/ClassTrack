<?php
header('Content-Type: application/json');
try {
    $data = [
        "totalUsuarios" => $totalUsuarios,
        "totalEstudiantes" => $totalEstudiantes
    ];
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
