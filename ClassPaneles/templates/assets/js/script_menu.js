function toggleMenu() {
    const menu = document.getElementById('menu-container');
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
}

// Función para mostrar/ocultar el submenú
function toggleDropdown() {
    const submenu = document.getElementById('submenu');
    submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
}