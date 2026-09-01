/**
 * SISTEMA INFORMÁTICO - PARQUE TECNOLÓGICO
 * Módulo: Estadísticas e Inteligencia de Parque
 * Archivo: js/estadistica.js
 */

// 📊 1. INICIALIZADOR GENERAL (Espera a que el DOM esté listo)
document.addEventListener("DOMContentLoaded", function() {
    const dataServer = window.DATA_ESTADISTICAS;
    
    if (!dataServer) {
        console.error("Error: No se encontraron los datos del servidor (DATA_ESTADISTICAS).");
        return;
    }

    // Inicializar pestaña por defecto desde la URL
    switchTab('tab-' + dataServer.activeTab);

    // Renderizar Gráficos Analíticos si existen datos
    initGraficoHardware(dataServer.hardware);
    initGraficoModelos(dataServer.modelos);
    initGraficoComponentes(dataServer.componentes);
    initGraficoCelulares(dataServer.celulares);
});

// 🔀 2. CONTROLADOR DE PESTAÑAS (TABS)
function switchTab(tabId) {
    // Ocultar todos los contenidos de pestañas
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Resetear estilos de todos los botones de pestañas
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-blue-600', 'text-blue-600');
        el.classList.add('border-transparent', 'text-slate-500');
    });
    
    // Mostrar pestaña seleccionada
    const targetTab = document.getElementById(tabId);
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }
    
    // Activar estilo visual en el botón correspondiente
    const currentBtn = document.getElementById('btn-' + tabId);
    if (currentBtn) {
        currentBtn.classList.remove('border-transparent', 'text-slate-500');
        currentBtn.classList.add('border-blue-600', 'text-blue-600');
    }
}

// 🔄 3. MOTOR ACORDEÓN PARA DETALLES DE COMPONENTES INTERNOS
function toggleComponentes(equipoId) {
    const row = document.getElementById('comp-row-' + equipoId);
    const icon = document.getElementById('icon-' + equipoId);
    
    if (!row || !icon) return;

    if (row.classList.contains('hidden')) {
        row.classList.remove('hidden');
        icon.innerText = '▼';
        icon.classList.add('rotate-90');
    } else {
        row.classList.add('hidden');
        icon.innerText = '▶';
        icon.classList.remove('rotate-90');
    }
}

// 🖥️ 4. CONSTRUCTOR: GRÁFICO HARDWARE (Barras Verticales)
function initGraficoHardware(data) {
    const ctx = document.getElementById('canvasHardware');
    if (!ctx || !data.labels.length) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Inversión Total ($)',
                    data: data.values, // Tus datos originales de dinero
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    yAxisID: 'y' // Usa el eje izquierdo normal
                },
                {
                    label: 'Cantidad (Unidades)',
                    data: data.cantidades, // 🌟 CORREGIDO: cambiado de data.quantities a data.cantidades
                    backgroundColor: '#10b981', // Un tono esmeralda para contrastar
                    borderRadius: 4,
                    yAxisID: 'y1' // Usa el nuevo eje derecho
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    display: true, // Ahora activamos la leyenda para distinguir qué es qué
                    position: 'top',
                    labels: { boxWidth: 12, font: { size: 11 } }
                } 
            },
            scales: { 
                y: { 
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: { display: true, text: 'Inversión ($)' }
                },
                y1: { 
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: { display: true, text: 'Cantidad' },
                    grid: { drawOnChartArea: false } // Evita que se crucen las líneas de cuadrícula internas
                }
            }
        }
    });
}


// 🖥️ 5. CONSTRUCTOR: GRÁFICO MODELOS CLAVE (Barras Horizontales Bi-Métrica)
function initGraficoModelos(data) {
    const ctx = document.getElementById('canvasModelos');
    if (!ctx || !data.labels.length) return;

    new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Inversión ($)',
                    data: data.precios,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4
                },
                {
                    label: 'Cantidad (Unds)',
                    data: data.cantidades,
                    backgroundColor: '#38bdf8',
                    borderRadius: 4
                }
            ]
        },
        options: {
            indexAxis: 'y', // Cambia la orientación a horizontal de forma limpia
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    display: true, 
                    position: 'top',
                    labels: { boxWidth: 12, font: { size: 10 } }
                } 
            },
            scales: { x: { beginAtZero: true } }
        }
    });
}

// 🧩 6. CONSTRUCTOR: GRÁFICO COMPONENTES (Área Polar Inteligente)
function initGraficoComponentes(data) {
    const ctx = document.getElementById('canvasComponentes');
    if (!ctx || !data.labels.length) return;

    new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.75)',  // 1. Azul (RAM)
                    'rgba(139, 92, 246, 0.75)',  // 2. Violeta (Disco Duro)
                    'rgba(16, 185, 129, 0.75)',  // 3. Esmeralda (SSD)
                    'rgba(245, 158, 11, 0.75)',  // 4. Ámbar / Oro (Procesador)
                    'rgba(239, 68, 68, 0.75)',   // 5. Rojo (Tarjeta de Video)
                    'rgba(236, 72, 153, 0.75)',  // 6. Rosa (Batería)
                    'rgba(6, 182, 212, 0.75)',   // 7. Cian (Cargador)
                    'rgba(20, 184, 166, 0.75)',  // 8. Teal / Turquesa (Mouse)
                    'rgba(249, 115, 22, 0.75)',  // 9. Naranja (Teclado)
                    'rgba(107, 114, 128, 0.75)', // 10. Gris Slate (Mouse Pad)
                    'rgba(132, 204, 22, 0.75)',  // 11. Lima (Ventilador)
                    'rgba(14, 165, 233, 0.75)',  // 12. Sky Blue (Otro)
                    'rgba(99, 102, 241, 0.75)',  // 13. Índigo
                    'rgba(217, 70, 239, 0.75)',  // 14. Fucsia
                    'rgba(234, 179, 8, 0.75)',   // 15. Amarillo
                    'rgba(161, 98, 7, 0.75)'     // 16. Marrón / Tierra
                ],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, font: { size: 9 } }
                }
            },
            scales: {
                r: {
                    ticks: { display: false }, // Oculta números radiales sobre la gráfica
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
}

// 📱 7. CONSTRUCTOR: GRÁFICO TELEFONÍA (Dona de Distribución)
function initGraficoCelulares(data) {
    const ctx = document.getElementById('canvasCelulares');
    if (!ctx || !data.labels.length) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: ['#34c7eb', '#10b981', '#f59e0b', '#ef4444', '#64748b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { font: { size: 10 }, boxWidth: 12 } 
                } 
            }
        }
    });
}