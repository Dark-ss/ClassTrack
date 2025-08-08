<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php';

$correo = $_SESSION['usuario'];

// Obtener el ID del admin basado en el correo
$query_usuario = "SELECT id FROM usuarios WHERE correo = ?";
$stmt_usuario = $conexion->prepare($query_usuario);
$stmt_usuario->bind_param("s", $correo);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows == 0) {
    echo "<script>alert('Usuario no encontrado.'); window.location.href='vista_cuentas.php';</script>";
    exit;
}

/*Paginación*/
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$query = "SELECT * FROM mensajes ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query);

//Calcular paginas 
$query_total = "SELECT COUNT(*) as total FROM mensajes";
$resultado_total_mensajes = mysqli_query($conexion, $query_total);
$total_mensajes = mysqli_fetch_assoc($resultado_total_mensajes )['total'];
$total_paginas = ceil($total_mensajes / $registros_por_pagina);

//busqueda de cuentas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$row_usuario = $result_usuario->fetch_assoc();

$sql = "SELECT m.id, u.nombre_completo AS remitente, m.mensaje, m.fecha_registro, m.nivel_prioridad, m.tipo, m.estado
        FROM mensajes m
        JOIN usuarios u ON m.id_remitente = u.id
        WHERE m.destinatario = 'admin'
        AND (m.fecha_registro LIKE '%$search%'
        OR m.nivel_prioridad LIKE '%$search%' 
        OR m.tipo LIKE '%$search%')";
        //AND (m.respuesta IS NULL OR m.respuesta = '')"; condición para mostrar peticiones no resueltas
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conexion->error);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Panel Administrador</title>
</head>

<body>
<div class="container">
<?php
$currentFile = basename($_SERVER['PHP_SELF']);
?>
        <aside class="sidebar">
            <div class="logo">
                <img src="../../assets/images/logo2.png" alt="Logo" class="logo-img" width="150" height="auto">
            </div>
            <nav class="menu">
                <div class="menu-group">
                    <p class="menu-title">Menú Principal</p>
                    <ul>
                        <li><a href="admin_dashboard.php"
                                class="<?php echo $currentFile == 'admin_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="vista_cuentas.php"
                                class="<?php echo $currentFile == 'vista_cuentas.php' ? 'active' : ''; ?>">
                                <ion-icon name="people-outline"></ion-icon> Cuentas
                            </a></li>
                        <li><a href="vista_students.php"
                                class="<?php echo $currentFile == 'vista_students.php' ? 'active' : ''; ?>">
                                <ion-icon name="school-outline"></ion-icon> Estudiantes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de Espacios</p>
                    <ul>
                        <li><a href="./register_buldings.php"
                                class="<?php echo $currentFile == 'register_buildings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Añadir Edificios
                            </a></li>
                        <li><a href="table_build.php"
                                class="<?php echo $currentFile == 'table_build.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="equipment.php"
                                class="<?php echo $currentFile == 'equipment.php' ? 'active' : ''; ?>">
                                <ion-icon name="construct-outline"></ion-icon> Equipamientos
                            </a></li>
                        <li><a href="table_reservation.php"
                                class="<?php echo $currentFile == 'table_reservation.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Mensajeria</p>
                    <ul>
                        <li><a href="messages.php"
                                class="<?php echo $currentFile == 'messages.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Buzon ayuda
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="../../php/config.php"
                                class="<?php echo $currentFile == 'config.php' ? 'active' : ''; ?>">
                                <ion-icon name="settings-outline"></ion-icon> Ajustes
                            </a></li>
                        <li><a href="../../php/cerrar_sesion.php"
                                class="<?php echo $currentFile == 'cerrar_sesion.php' ? 'active' : ''; ?>">
                                <ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión
                            </a></li>
                    </ul>
                </div>
            </nav>
            <div class="divider"></div>
            <div class="profile">
                <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
                <div>
                    <p class="user-name"><?php echo htmlspecialchars($nombre_completo); ?></p>
                    <p class="user-email"> <?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="content">
        <h2>Mensajes Recibidos</h2>

        <div class="search-and-create">
                <form method="GET" action="messages.php" class="search-form">
                    <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    <input type="text" name="buscar" placeholder="Buscar mensajes..."
                        value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>
        </div>

        <div class="table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Remitente</th>
                        <th>Mensaje</th>
                        <th>Nivel prioridad</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo ($row['estado'] === 'Resuelto') ? 'Resuelto' : ''; ?>">
                        <td><?php echo htmlspecialchars($row['remitente']); ?></td>
                        <td><?php echo htmlspecialchars($row['mensaje']); ?></td>
                        <td><?php echo htmlspecialchars($row['nivel_prioridad']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_registro']); ?></td>
                        <td><?php echo htmlspecialchars($row['estado']); ?></td>
                        <td>
                            <div class="dropdown">
                                <ion-icon name="ellipsis-horizontal-sharp" class="dropdown-toggle"></ion-icon>
                                <div class="dropdown-content">  
                                    <button type="button" class="btn-reject" onclick="mostrarMotivoRechazo(<?php echo $row['id']; ?>)">
                                        <ion-icon name="chatbox-outline"></ion-icon> Responder
                                    </button>
                                    <button type="button" class="btn-reject" onclick="eliminarMensaje(<?php echo $row['id']; ?>)">
                                        <ion-icon name="trash-outline"></ion-icon> Eliminar
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- MODAL -->
        <div id="motivoModal" class="modal-reject">
            <div class="modal-content-reject">
                <h2>Responder Mensaje</h2>
                <form id="respuestaForm">
                    <textarea id="motivo-text" name="respuesta" class="motivo_text" placeholder="Escriba la respuesta"></textarea>
                    <input type="hidden" id="mensaje-id" name="mensaje_id">
                    <div>
                        <button type="button" class="close-reject" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="button-reject">Enviar Respuesta</button>
                    </div>
                </form>
            </div>
        </div>


        <div class="pagination">
                <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="pagination-button">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="pagination-button <?php echo $i === $pagina_actual ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="pagination-button">Siguiente</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
<script>
function mostrarMotivoRechazo(idMensaje) {
    document.getElementById("mensaje-id").value = idMensaje; // Se asigna correctamente el ID
    document.getElementById("motivoModal").style.display = "block";
}

// Cerrar modal
function closeModal() {
    document.getElementById("motivoModal").style.display = "none";
}

// Enviar respuesta
document.getElementById("respuestaForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let motivoInput = document.getElementById("motivo-text").value.trim();
    let mensajeId = document.getElementById("mensaje-id").value.trim();

    if (motivoInput === "" || mensajeId === "") {
        alert("Por favor, ingrese una respuesta válida.");
        return;
    }

    fetch("responder_mensaje.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `mensaje_id=${encodeURIComponent(mensajeId)}&respuesta=${encodeURIComponent(motivoInput)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Respuesta enviada correctamente.");
            window.location.reload(); // Recargar para actualizar estado
        } else {
            alert("Error al enviar la respuesta: " + data.error);
        }
    })
    .catch(error => {
        console.error("Error en la solicitud:", error);
        alert("Hubo un problema con la solicitud.");
    });

    closeModal();
});

function eliminarMensaje(mensajeId) {
    if (!mensajeId) {
        alert("No se pudo obtener el ID del mensaje.");
        return;
    }

    if (!confirm("¿Estás seguro de que deseas eliminar este mensaje?")) {
        return;
    }

    fetch("delete_messages.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `mensaje_id=${encodeURIComponent(mensajeId)}`
    })
    .then(response => response.text()) // <-- Obtener texto en vez de JSON
    .then(text => {
        console.log("Respuesta del servidor:", text);
        return JSON.parse(text); // Intentar parsear JSON manualmente
    })
    .then(data => {
        if (data.success) {
            alert("Mensaje eliminado correctamente.");
            window.location.reload(); // Recargar la página para actualizar la lista
        } else {
            alert("Error al eliminar el mensaje: " + data.error);
        }
    })
    .catch(error => {
        console.error("Error en la solicitud:", error);
        alert("Hubo un problema con la eliminación.");
    });
}



    </script>
</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</html>