<?php
include '../../php/admin_session.php'; // Verifica que el admin esté autenticado

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); // Redirige a no admin
    exit();
}
include '../../php/conexion_be.php';

//paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

//Calcular paginas 
$query_total = "SELECT COUNT(*) as total FROM edificios";
$resultado_total = mysqli_query($conexion, $query_total);
$total_usuarios = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

$query = "SELECT * FROM edificios ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query);

//busqueda de cuentas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$query = "SELECT id, imagen, nombre, codigo, pisos, cupo, direccion, tipo FROM 
        edificios WHERE nombre LIKE '%$search%' OR codigo LIKE '%$search%' OR 
        tipo LIKE '%$search%'";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id != $_SESSION['id']) {

        $query_delete_espacios = "DELETE FROM espacios_academicos WHERE edificio_id = '$id'";
        mysqli_query($conexion, $query_delete_espacios);

        $query_delete = "DELETE FROM edificios WHERE id = '$id'";
        mysqli_query($conexion, $query_delete);
        header("Location: table_build.php");
        exit();
    } else {
        echo "<script>alert('No puedes eliminarlo.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Lista Edificios</title>
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
                    <a href="table_reservation.php">Reservas</a>
                </div>
            </div>
        </div>
        <form method="GET" action="table_build.php" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar edificio..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
        <h1 class="title-table">Lista de Edificios</h1>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Codigo</th>
                    <th>Pisos</th>
                    <th>Cupo</th>
                    <th>Dirección</th>
                    <th>Tipo</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id']); ?></td>
                        <td><img src="<?php echo $fila['imagen'] ? $fila['imagen'] : '../../uploads/usuario.png'; ?>" alt="Imagen de Edificio" width="50"></td>
                        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['pisos']); ?></td>
                        <td><?php echo htmlspecialchars($fila['cupo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                        <td>
                            <?php
                                $query_espacios = "SELECT COUNT(*) as total FROM espacios_academicos WHERE edificio_id = {$fila['id']}";
                                $resultado_espacios = mysqli_query($conexion, $query_espacios);
                                $total_espacios = mysqli_fetch_assoc($resultado_espacios)['total'];
                                $mensaje_confirmacion = $total_espacios > 0
                                    ? "¿Estás seguro de que deseas eliminar este edificio? ¡Hay $total_espacios espacios asociados!"
                                    : "¿Estás seguro de que deseas eliminar este edificio?";
                                
                            ?>
                            <a href="?id=<?php echo $fila['id']; ?>" class="delete-button" onclick="return confirm('<?php echo $mensaje_confirmacion; ?>');">
                                <img src="../../assets/images/delete.png" alt="Eliminar" class="icons-image">
                            </a>
                            <a href="update_building.php?id=<?php echo $fila['id']; ?>" class="delete-button">
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
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>
<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>