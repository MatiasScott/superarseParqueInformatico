// ===============================
// MODAL
// ===============================
function openModal() {

    document
        .getElementById('modalEquipo')
        .classList.remove('hidden');
}

function closeModal() {

    document
        .getElementById('modalEquipo')
        .classList.add('hidden');
}


// ===============================
// BUSCADOR
// ===============================
document
    .getElementById('searchInput')
    ?.addEventListener('keyup', function () {

        let filtro = this.value.toLowerCase();

        let filas = document.querySelectorAll('#tablaEquipos tbody tr');

        filas.forEach(fila => {

            let texto = fila.innerText.toLowerCase();

            fila.style.display = texto.includes(filtro)
                ? ''
                : 'none';
        });
});