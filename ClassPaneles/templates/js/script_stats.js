async function fetchStats() {
    try {
        const response = await fetch('get_stats.php'); //Json
        const data = await response.json();

        document.getElementById('totalUsers').querySelector('p').textContent = data.totalUsuarios;
        document.getElementById('totalStudents').querySelector('p').textContent = data.totalEstudiantes;
    } catch (error) {
        console.error("Error al obtener estadísticas:", error);
    }
}
// Actualiza las estadísticas cada 30sg
setInterval(fetchStats, 30000);