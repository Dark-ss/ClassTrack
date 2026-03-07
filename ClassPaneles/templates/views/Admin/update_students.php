<?php
require_once '../../php/conexion_be.php';
include '../../php/admin_session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vista_students.php');
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Location: vista_students.php');
    exit;
}

$id = mysqli_real_escape_string($conexion, $_POST['id']);

$id = mysqli_real_escape_string($conexion, $_POST['id']);

$query_usuario = "SELECT id FROM estudiantes WHERE id = '$id'";
$resultado_usuario = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_usuario) === 0) {
    header('Location: vista_cuentas.php');
    exit;
}

include '../../php/update_table_students.php';

header("Location: vista_students.php?update=success");
exit();