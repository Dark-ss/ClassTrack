<?php
include '../../php/admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php");
    exit();
}
include '../../php/conexion_be.php';

//paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

//Calcular paginas 
$query_total = "SELECT COUNT(*) as total FROM estudiantes";
$resultado_total = mysqli_query($conexion, $query_total);
$total_usuarios = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

$query = "SELECT * FROM estudiantes ORDER BY fecha_registro DESC";
$resultado = mysqli_query($conexion, $query);

//busqueda de cuentas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$query = "SELECT id, imagen, nombre_completo, correo, identificacion, fecha_registro FROM 
        estudiantes WHERE nombre_completo LIKE '%$search%' OR correo LIKE '%$search%' OR 
        identificacion LIKE '%$search%'";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id != $_SESSION['id']) {
        $query_delete = "DELETE FROM estudiantes WHERE id = '$id'";
        mysqli_query($conexion, $query_delete);
        header("Location: vista_students.php");
        exit();
    } else {
        echo "<script>alert('No puedes eliminar tu propia cuenta.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Estudiantes</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
</head>

<body>
    <main>
        <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="../../php/cerrar_sesion.php" class="logout">
                <img src="../../assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            <a href="../../php/config.php" class="config">
                <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="admin_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>

            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">Cuenta<span>▼</span>
                </div>
                <div class="submenu" id="submenu">
                    <a href="create_account.php">Crear Cuenta</a>
                    <a href="vista_cuentas.php">cuentas </a>
                    <a href="register_students.php">Añadir Estudiantes</a>
                    <a href="vista_students.php">Estudiantes</a>
                </div>
            </div>
            <div class="menu-container_espacios" id="menu-container_espacios">
                <div class="menu-link" onclick="toggleDropdown_space()">Espacios<span>▼</span>
                </div>
                <div class="submenu" id="submenu_espacios">
                    <a href="register_buldings.php">Añadir Edificios</a>
                    <a href="table_build.php">Edificios</a>
                    <a href="equipment.php">Equipamientos</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>
        <form method="GET" action="vista_students.php" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar estudiante..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
        <h1 class="title-table">Lista de Estudiantes</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Identificación</th>
                    <th>Fecha de Registro</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id']); ?></td>
                        <td>
                            <img src="<?php echo $fila['imagen'] ? $fila['imagen'] : '../../uploads/usuario.png'; ?>" alt="Imagen de Estudiante" width="50">
                        </td>
                        <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['identificacion']); ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha_registro']); ?></td>
                        <td>
                            <a href="?id=<?php echo $fila['id']; ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                <img src="../../assets/images/delete.png" alt="Configuracion" class="icons-image"></a>
                            <a href="update_students.php?id=<?php echo $fila['id']; ?>" class="delete-button">
                                <img src="../../assets/images/update.png" alt="Configuracion" class="icons-image">
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!--Paginación-->
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="<?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </main>
    <script src="../../assets/js/script_stats.js"></script>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>
<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>