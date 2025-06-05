document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de pestañas
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Eliminar clase active de todos los botones
            tabButtons.forEach(btn => btn.classList.remove('active'));

            // Ocultar todos los contenidos
            tabContents.forEach(content => content.style.display = 'none');

            // Añadir clase active al botón clicado
            this.classList.add('active');

            // Mostrar el contenido correspondiente
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).style.display = 'block';
        });
    });

    // Variables globales
    const editModeBtn = document.getElementById('edit-mode-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const mainContent = document.getElementById('main-content');
    const editForm = document.getElementById('edit-form');
    const originalValues = {};
    const imagenInput = document.getElementById('imagen');
    const buildingImage = document.querySelector('.building-image');
    const saveBtn = document.querySelector('.save-btn');

    // Guardar los valores originales para poder restaurarlos
    function saveOriginalValues() {
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            originalValues[input.id] = input.value;
        });
        // Guardar también la imagen original
        originalValues['building-image'] = buildingImage.src;
    }

    // Restaurar los valores originales
    function restoreOriginalValues() {
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (originalValues[input.id]) {
                input.value = originalValues[input.id];
            }
        });
        // Restaurar la imagen original
        buildingImage.src = originalValues['building-image'];
    }

    // Configurar campos sincronizados
    function setupSyncedFields() {
        const syncedFields = document.querySelectorAll('[data-sync-with]');
        syncedFields.forEach(field => {
            const targetId = field.getAttribute('data-sync-with');
            const targetField = document.getElementById(targetId);

            field.addEventListener('input', function() {
                targetField.value = this.value;
            });

            targetField.addEventListener('input', function() {
                field.value = this.value;
            });
        });
    }

    // Event Listeners
    function setupEventListeners() {
        // Activar modo edición
        editModeBtn.addEventListener('click', function() {
            saveOriginalValues();
            mainContent.classList.add('edit-mode');
        });

        // Cancelar edición
        cancelEditBtn.addEventListener('click', function() {
            restoreOriginalValues();
            mainContent.classList.remove('edit-mode');
        });

        // Cambio de imagen
        if (imagenInput) {
            imagenInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        buildingImage.src = e.target.result;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        // Guardar cambios
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                editForm.submit();
            });
        }
    }

    // Inicialización
    function init() {
        setupSyncedFields();
        setupEventListeners();
    }

    init();
});

