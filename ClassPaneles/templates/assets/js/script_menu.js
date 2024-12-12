//Menú cuentas
function toggleMenu() {
    const menu = document.getElementById('menu-container');
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
}

function toggleDropdown() {
    const submenu = document.getElementById('submenu');
    const menuContainerEspacios = document.getElementById('menu-container_espacios');
    if (submenu.style.display === 'block') {
        // Si está abierto, cerrarlo y restaurar el margen
        submenu.style.display = 'none';
        menuContainerEspacios.style.marginTop = '180px';
    } else {
        // Si está cerrado, abrirlo y ajustar el margen del siguiente menú
        submenu.style.display = 'block';
        menuContainerEspacios.style.marginTop = '290px';
    }
}

//Menú espacios academicos
function toggleMenu_space() {
    const menu = document.getElementById('menu-container_espacios');
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
}

function toggleDropdown_space() {
    const submenu = document.getElementById('submenu_espacios');
    const menuContainerCuentas = document.getElementById('menu-container');
    if (submenu.style.display === 'block') {
        // Si está abierto, cerrarlo y restaurar el margen
        submenu.style.display = 'none';
        menuContainerCuentas.style.marginTop = '140px';
    } else {
        // Si está cerrado, abrirlo y ajustar el margen del menú de arriba
        submenu.style.display = 'block';
        menuContainerCuentas.style.marginTop = '140px';
    }
}