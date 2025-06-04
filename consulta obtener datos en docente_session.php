[1mdiff --git a/ClassPaneles/templates/php/docente_session.php b/ClassPaneles/templates/php/docente_session.php[m
[1mindex 83e488c..317c279 100644[m
[1m--- a/ClassPaneles/templates/php/docente_session.php[m
[1m+++ b/ClassPaneles/templates/php/docente_session.php[m
[36m@@ -8,11 +8,12 @@[m [minclude '../../php/conexion_be.php';[m
 [m
 // Obtener los datos del usuario[m
 $correo = $_SESSION['usuario']; // Usamos el correo de la sesión para obtener los datos del usuario[m
[31m-$query = "SELECT imagen, nombre_completo, rol FROM usuarios WHERE correo='$correo'"; // Consulta obtener datos[m
[32m+[m[32m$query = "SELECT id, imagen, nombre_completo, rol FROM usuarios WHERE correo='$correo'"; // Consulta obtener datos[m
 $resultado = mysqli_query($conexion, $query);[m
 [m
 if ($resultado && mysqli_num_rows($resultado) > 0) {[m
     $usuario_data = mysqli_fetch_assoc($resultado);[m
[32m+[m[32m    $_SESSION['id_usuario'] = $usuario_data['id'];[m
     // condicional imagen [m
     $imagen = $usuario_data['imagen'] ? "../../uploads/" . $usuario_data['imagen'] : "../../uploads/usuario.png";[m
     $nombre_completo = $usuario_data['nombre_completo'];[m
[1mdiff --git a/ClassPaneles/templates/views/Docente/docente_dashboard.php b/ClassPaneles/templates/views/Docente/docente_dashboard.php[m
[1mindex ad7225b..33a4f24 100644[m
[1m--- a/ClassPaneles/templates/views/Docente/docente_dashboard.php[m
[1m+++ b/ClassPaneles/templates/views/Docente/docente_dashboard.php[m
[36m@@ -45,7 +45,7 @@[m [m$totalEstudiantes = mysqli_fetch_assoc($resultEstudiantes)['total_estudiantes'];[m
                 </div>  [m
                 <div class="submenu" id="submenu">[m
                     <a href="vista_buildings.php">Edificios</a>[m
[31m-                    <a href="vista_students.php">Salones</a>[m
[32m+[m[32m                    <a href="reservation.php">Salones</a>[m
                 </div>[m
             </div>[m
 [m
[1mdiff --git a/ClassPaneles/templates/views/Docente/reservation.php b/ClassPaneles/templates/views/Docente/reservation.php[m
[1mnew file mode 100644[m
[1mindex 0000000..e69de29[m
[1mdiff --git a/ClassPaneles/templates/views/Docente/update_spaces_docente.php b/ClassPaneles/templates/views/Docente/update_spaces_docente.php[m
[1mindex b4862d5..2548fec 100644[m
[1m--- a/ClassPaneles/templates/views/Docente/update_spaces_docente.php[m
[1m+++ b/ClassPaneles/templates/views/Docente/update_spaces_docente.php[m
[36m@@ -32,7 +32,8 @@[m [mif ($_SERVER['REQUEST_METHOD'] === 'POST') {[m
         // Actualiza solo la descripción[m
         $query_update = "UPDATE espacios_academicos SET descripcion_general='$descripcion_general' WHERE id='$space_id'";[m
         if (mysqli_query($conexion, $query_update)) {[m
[31m-            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='vista_spaces_docente.php';</script>";[m
[32m+[m[32m            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='vista_space[m
[32m+[m[32m            s_docente.php';</script>";[m
         } else {[m
             echo "<script>alert('Error al actualizar la descripción: " . mysqli_error($conexion) . "');</script>";[m
         }[m
[36m@@ -90,17 +91,58 @@[m [m$espacios = [];[m
 while ($row = mysqli_fetch_assoc($result)) {[m
     $espacios[] = $row;[m
 }[m
[31m-[m
 // Validar si el ID corresponde a un edificio existente[m
[31m-$query_edificio = "SELECT nombre FROM edificios WHERE id = '$building_id'";[m
[31m-$result_edificio = mysqli_query($conexion, $query_edificio);[m
[32m+[m[32m$query = "SELECT nombre FROM edificios WHERE id = '$building_id'";[m
[32m+[m[32m$result = mysqli_query($conexion, $query);[m
 [m
[31m-if ($result_edificio && mysqli_num_rows($result_edificio) > 0) {[m
[31m-    $edificio = mysqli_fetch_assoc($result_edificio);[m
[32m+[m[32mif ($result && mysqli_num_rows($result) > 0) {[m
[32m+[m[32m    $edificio = mysqli_fetch_assoc($result);[m
 } else {[m
     echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_spaces_docente.php';</script>";[m
     exit;[m
 }[m
[32m+[m[32m//Reservación[m
[32m+[m[32mif ($_SERVER['REQUEST_METHOD'] === 'POST') {[m
[32m+[m[32m    if (isset($_POST['reserve_space'])) {[m
[32m+[m[32m        $id_usuario = mysqli_real_escape_string($conexion, $_POST['id_usuario']);[m
[32m+[m[32m        $fecha_inicio = date('Y-m-d H:i:s', strtotime($_POST['fecha_inicio']));[m
[32m+[m[32m        $fecha_final = date('Y-m-d H:i:s', strtotime($_POST['fecha_final']));[m
[32m+[m[32m        $tipo_reservacion = mysqli_real_escape_string($conexion, $_POST['tipo_reservacion']);[m
[32m+[m[32m        $descripcion_reserva = mysqli_real_escape_string($conexion, $_POST['descripcion']);[m
[32m+[m[32m        $space_id = mysqli_real_escape_string($conexion, $_POST['id_espacio']);[m
[32m+[m[41m        [m
[32m+[m[32m        $query_reserva = "INSERT INTO reservaciones (id_usuario, fecha_inicio, fecha_final, tipo_reservacion, descripcion, id_espacio)[m
[32m+[m[32m                VALUES ('$id_usuario', '$fecha_inicio', '$fecha_final', '$tipo_reservacion', '$descripcion_reserva', '$space_id')";[m
[32m+[m[32m        if (mysqli_query($conexion, $query_reserva)) {[m
[32m+[m[32m            echo "<script>alert('Reserva realizada con éxito.'); window.location.href='update_spaces_docente.php?id=" . $space_id . "';</script>";[m
[32m+[m[32m        } else {[m
[32m+[m[32m            echo "<script>alert('Error al realizar la reserva: " . mysqli_error($conexion) . "');</script>";[m
[32m+[m[32m        }[m
[32m+[m[32m    }[m
[32m+[m[32m}[m
[32m+[m
[32m+[m[32m// Validar si el ID corresponde a un edificio existente[m
[32m+[m[32m$query_reserva = "SELECT codigo FROM espacios_academicos WHERE id = $space_id";[m
[32m+[m[32m$result_reserva = mysqli_query($conexion, $query_reserva);[m
[32m+[m
[32m+[m[32mif ($result_reserva && mysqli_num_rows($result_reserva) > 0) {[m
[32m+[m[32m    $espacio_reserva = mysqli_fetch_assoc($result_reserva);[m
[32m+[m[32m}   else {[m
[32m+[m[32m    echo "<script>alert('Espacio no encontrado. ID: $space_id'); window.location.href='update_spaces_docente.php';</script>";[m
[32m+[m[32m    exit;[m
[32m+[m[32m}[m
[32m+[m
[32m+[m[32m$id_usuario = $_SESSION['id_usuario'];[m
[32m+[m[32m// Validar si el ID corresponde a un edificio existente[m
[32m+[m[32m$query_usuario = "SELECT id, nombre_completo FROM usuarios WHERE id = $id_usuario";[m
[32m+[m[32m$result_usuario = mysqli_query($conexion, $query_usuario);[m
[32m+[m
[32m+[m[32mif ($result_usuario && mysqli_num_rows($result_usuario) > 0) {[m
[32m+[m[32m    $espacio_usuario = mysqli_fetch_assoc($result_usuario);[m
[32m+[m[32m}   else {[m
[32m+[m[32m    echo "<script>alert('Usuario no encontrado. ID: $id_usuario'); window.location.href='update_spaces_docente.php';</script>";[m
[32m+[m[32m    exit;[m
[32m+[m[32m}[m
 ?>[m
 [m
 <!DOCTYPE html>[m
[36m@@ -145,6 +187,7 @@[m [mif ($result_edificio && mysqli_num_rows($result_edificio) > 0) {[m
             <div class="image-container">[m
                 <h1 class="title_build"><?php echo htmlspecialchars($id['codigo']); ?></h1>[m
                 <img src="<?php echo htmlspecialchars($id['imagen']); ?>" alt="Edificio" class="profile-img-build">[m
[32m+[m[32m                <button type="button" class="button-space" onclick="openModal()">Reservar</button>[m
             </div>[m
 [m
         <form method="POST" enctype="multipart/form-data" class="description-form">[m
[36m@@ -219,8 +262,75 @@[m [mif ($result_edificio && mysqli_num_rows($result_edificio) > 0) {[m
     </div>[m
     </form>[m
     </div>[m
[32m+[m
[32m+[m[32m    <div class="modal" id="modal">[m
[32m+[m[32m    <div class="modal-content">[m
[32m+[m[32m    <h2>Formulario de Reserva</h2>[m
[32m+[m[32m        <form id="reserve-form" method="POST">[m
[32m+[m[32m            <input type="hidden" name="reserve_space" value="true">[m
[32m+[m[32m            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id['id']); ?>">[m
[32m+[m[32m            <input type="hidden" name="id_espacio" value="<?php echo htmlspecialchars($space_id); ?>">[m
[32m+[m[32m            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario) ?>">[m
[32m+[m
[32m+[m[32m            <div class="form-group-container">[m
[32m+[m[32m                <div class="form-group">[m
[32m+[m[32m                    <label for="id_usuario">Nombre Del Solicitante:</label>[m
[32m+[m[32m                    <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($espacio_usuario['nombre_completo']); ?>">[m
[32m+[m[32m                </div>[m
[32m+[m[41m                [m
[32m+[m[32m                <div class="form-group">[m
[32m+[m[32m                    <label for="fecha_inicio">Fecha y hora de inicio:</label>[m
[32m+[m[32m                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>[m
[32m+[m[32m                </div>[m
[32m+[m[32m            </div>[m
[32m+[m[32m            <div class="form-group-container">[m
[32m+[m[32m                <div class="form-group">[m
[32m+[m[32m                    <label for="fecha_final">Fecha y hora de fin:</label>[m
[32m+[m[32m                    <input type="datetime-local" id="fecha_final" name="fecha_final" required>[m
[32m+[m[32m                </div>[m
[32m+[m
[32m+[m[32m                <div class="form-group">[m
[32m+[m[32m                <label for="tipo_reservacion">tipo de reservacion:</label>[m
[32m+[m[32m                    <select id="tipo_reservacion" name="tipo_reservacion" required>[m
[32m+[m[32m                        <option value="">Seleccione el tipo</option>[m
[32m+[m[32m                        <option value="Clase">Clase</option>[m
[32m+[m[32m                        <option value="Reunion">Reunion</option>[m
[32m+[m[32m                        <option value="Evento">Evento</option>[m
[32m+[m[32m                    </select>[m
[32m+[m[32m                </div>[m
[32m+[m[32m            </div>[m
[32m+[m[32m            <div class="form-group-container">[m
[32m+[m[32m                <div class="form-group">[m
[32m+[m[32m                        <label for="descripcion">Descripción reserva:</label>[m
[32m+[m[32m                        <textarea id="descripcion" name="descripcion" class="description-register" rows="4" required></textarea>[m
[32m+[m[32m                </div>[m
[32m+[m[32m            </div>[m
[32m+[m
[32m+[m[32m            <div class="form-group-container">[m
[32m+[m[32m                <div class="form-group">[m
[32m+[m[32m                    <label>Espacio:</label>[m
[32m+[m[32m                    <input type="number" value="<?php echo htmlspecialchars($espacio_reserva['codigo']); ?>">[m
[32m+[m[32m                </div>[m
[32m+[m[32m            </div>[m
[32m+[m[32m            <div class="form-group">[m
[32m+[m[32m                <button type="submit">Reservar espacio</button>[m
[32m+[m[32m            </div>[m
[32m+[m[32m        </form>[m
[32m+[m[32m    </div>[m
[32m+[m[32m    </div>[m
 </main>[m
[32m+[m[32m<script>[m
[32m+[m[32m        function openModal() {[m
[32m+[m[32m            document.getElementById("modal").style.display = "block";[m
[32m+[m[32m        }[m
 [m
[32m+[m[32m        // Cerrar el modal cuando se haga clic fuera del modal[m
[32m+[m[32m        window.onclick = function(event) {[m
[32m+[m[32m            if (event.target === document.getElementById("modal")) {[m
[32m+[m[32m                document.getElementById("modal").style.display = "none";[m
[32m+[m[32m            }[m
[32m+[m[32m        }[m
[32m+[m[32m</script>[m
 <script src="../../assets/js/button_update.js"></script>[m
 <script src="../../assets/js/script_menu.js"></script>[m
 </body>[m
[1mdiff --git a/ClassPaneles/templates/views/Docente/vista_spaces_docente.php b/ClassPaneles/templates/views/Docente/vista_spaces_docente.php[m
[1mindex 56c5c27..64dbcdd 100644[m
[1m--- a/ClassPaneles/templates/views/Docente/vista_spaces_docente.php[m
[1m+++ b/ClassPaneles/templates/views/Docente/vista_spaces_docente.php[m
[36m@@ -63,18 +63,18 @@[m [m$result = mysqli_query($conexion, $query);[m
 if ($result && mysqli_num_rows($result) > 0) {[m
     $edificio = mysqli_fetch_assoc($result);[m
 }   else {[m
[31m-    echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_edificios.php';</script>";[m
[32m+[m[32m    echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_buildings.php';</script>";[m
     exit;[m
 }[m
 [m
 if (isset($_GET['edificio_id'])) {[m
     $building_id = intval($_GET['edificio_id']);[m
     if ($building_id <= 0) {[m
[31m-        echo "<script>alert('ID de edificio no válido.'); window.location.href='vista_edificios.php';</script>";[m
[32m+[m[32m        echo "<script>alert('ID de edificio no válido.'); window.location.href='vista_buildings.php';</script>";[m
         exit;[m
     }[m
 } else {[m
[31m-    echo "<script>alert('ID de edificio no especificado.'); window.location.href='vista_edificios.php';</script>";[m
[32m+[m[32m    echo "<script>alert('ID de edificio no especificado.'); window.location.href='vista_buildings.php';</script>";[m
     exit;[m
 }[m
 //consulta edificio, separación de espacios[m
