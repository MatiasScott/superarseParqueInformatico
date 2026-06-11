/**
 * Buscador Asíncrono en Tiempo Real para Módulo de Telefonía e Inventario
 * Filtra directamente sobre el DOM sin recargar la página
 */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const tablaTelefonia = document.getElementById('tablaTelefonia');

    if (searchInput && tablaTelefonia) {
        const tbody = tablaTelefonia.querySelector('tbody');
        
        // Capturamos las filas de datos reales (ignoramos filas de mensajes si las hubiera de respaldo)
        const filas = tbody.querySelectorAll('tr:not(.no-results-row)');

        // 1. Crear e insertar la fila dinámica de "Sin resultados" 
        const filaNoResultados = document.createElement('tr');
        filaNoResultados.className = 'no-results-row hidden';
        filaNoResultados.innerHTML = `
            <td colspan="6" class="p-10 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="bg-slate-100 p-4 rounded-full mb-3 text-slate-400">
                        <i class="ph ph-magnifying-glass text-3xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">No se encontraron líneas o terminales</h3>
                    <p class="text-xs text-slate-400 mt-1">Intente cambiar los criterios o términos de su búsqueda.</p>
                </div>
            </td>
        `;
        tbody.appendChild(filaNoResultados);

        // Función medular de filtrado
        const filtrarTabla = (valorInput) => {
            const termino = valorInput.toLowerCase().trim();
            let coincidencias = 0;

            // Si el buscador está vacío, mostramos todo y ocultamos el botón "X"
            if (termino === '') {
                filas.forEach(fila => fila.classList.remove('hidden'));
                filaNoResultados.classList.add('hidden');
                if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
                return;
            }

            // Mostrar el botón de limpiar si hay texto escrito
            if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');

            // Recorrer filas analizando el texto interno de las celdas
            filas.forEach(fila => {
                const textoFila = fila.innerText.toLowerCase();

                if (textoFila.includes(termino)) {
                    fila.classList.remove('hidden');
                    coincidencias++;
                } else {
                    fila.classList.add('hidden');
                }
            });

            // Si no hubo ninguna coincidencia, activamos la fila de aviso
            if (coincidencias === 0) {
                filaNoResultados.classList.remove('hidden');
            } else {
                filaNoResultados.classList.add('hidden');
            }
        };

        // 2. Escuchar el evento de escritura 'input' (Tiempo real total)
        searchInput.addEventListener('input', (e) => {
            filtrarTabla(e.target.value);
        });

        // 3. Acción del botón limpiar ("X")
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                filtrarTabla('');
                searchInput.focus(); // Devuelve el foco al input para comodidad del usuario
            });
        }
    }
});