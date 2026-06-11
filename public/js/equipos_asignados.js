/**
 * SISTEMA INFORMÁTICO - PARQUE TECNOLÓGICO
 * Módulo: Reporte de Equipos Asignados (Alpine.js Component)
 * Archivo: js/equipos_asignados.js
 */

function reporteAsignadosComponent() {
    return {
        search: '',
        filterArea: 'TODAS',
        // Carga de forma segura los datos inyectados por el servidor o un array vacío si falla
        items: window.DATA_EQUIPOS_ASIGNADOS || [],
        
        // 🔍 Filtro inteligente en tiempo real
        get filteredItems() {
            return this.items.filter(item => {
                const searchTxt = this.search.toLowerCase();
                
                const matchSearch = 
                    (item.equipo_nombre || '').toLowerCase().includes(searchTxt) ||
                    (item.equipo_tipo || '').toLowerCase().includes(searchTxt) ||
                    (item.equipo_marca || '').toLowerCase().includes(searchTxt) ||
                    (item.equipo_serie || '').toLowerCase().includes(searchTxt) ||
                    (item.colaborador_nombre || '').toLowerCase().includes(searchTxt) ||
                    (item.codigo_acta || '').toLowerCase().includes(searchTxt);
                    
                const matchArea = this.filterArea === 'TODAS' || item.colaborador_area === this.filterArea;
                
                return matchSearch && matchArea;
            });
        },
        
        // 📅 Formateador de Fechas (Convierte de YYYY-MM-DD a DD/MM/YYYY)
        formataFecha(fechaRaw) {
            if (!fechaRaw) return '';
            const partes = fechaRaw.split('-');
            if (partes.length !== 3) return fechaRaw;
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
    }
}