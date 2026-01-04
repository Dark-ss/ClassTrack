<?php
require_once '../../php/conexion_be.php';
include '../../php/admin_session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vista_cuentas.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: vista_cuentas.php');
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

$query_usuario = "SELECT id FROM usuarios WHERE id = '$id'";
$resultado_usuario = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_usuario) === 0) {
    header('Location: vista_cuentas.php');
    exit;
}

include '../../php/update_table.php';

header('Location: vista_cuentas.php');
exit;