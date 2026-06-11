// Esperar a que el HTML cargue completamente
document.addEventListener('DOMContentLoaded', function() {

    // 1. LÓGICA DEL BUSCADOR
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('#tablaColaboradores tbody');

    // Verificamos que los elementos existan para no dar error en otras páginas
    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            let rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) 
                    ? '' 
                    : 'none';
            });
        });
    }
});

// 2. FUNCIONES DE MODAL (Fuera del DOMContentLoaded para que sean globales)
function openModal() {
    const modal = document.getElementById('modalColaborador');
    if(modal) modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('modalColaborador');
    if(modal) modal.classList.add('hidden');
}