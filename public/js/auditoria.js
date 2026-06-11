/**
 * SISTEMA INFORMÁTICO - PARQUE TECNOLÓGICO
 * Módulo: Bitácora de Auditoría y Seguridad (Alpine.js Component)
 * Archivo: js/auditoria.js
 */

function bitacoraAuditoriaComponent() {
    return {
        search: '',
        filterAccion: 'TODOS',
        // Carga de forma segura los logs del servidor
        logs: window.DATA_AUDITORIA_LOGS || [],
        
        // Estado Interno del Modal de Detalle
        modalOpen: false,
        modalData: { 
            tabla: '', 
            registroId: '', 
            anterior: '', 
            nuevo: '' 
        },

        // 🔍 Filtro y buscador en tiempo real sobre la bitácora
        get filteredLogs() {
            return this.logs.filter(log => {
                const query = this.search.toLowerCase();
                
                const matchSearch = 
                    (log.tabla_afectada || '').toLowerCase().includes(query) ||
                    (log.usuario_nombre || '').toLowerCase().includes(query) ||
                    (log.ip_origen || '').toLowerCase().includes(query) ||
                    (log.registro_id || '').toString().includes(query);
                    
                const matchAccion = this.filterAccion === 'TODOS' || log.accion === this.filterAccion;
                
                return matchSearch && matchAccion;
            });
        },

        // 👁️ Abre el modal y formatea las cargas estructuradas de JSON (Campos Diff)
        abrirDetalle(log) {
            // Helper para parsear de manera segura el JSON y dejarlo formateado para lectura humana
            const formatearJSON = (str) => {
                if (!str) return 'N/A (No hay registros)';
                try {
                    const obj = typeof str === 'string' ? JSON.parse(str) : str;
                    return JSON.stringify(obj, null, 4);
                } catch (e) {
                    return str;
                }
            };

            this.modalData.tabla = log.tabla_afectada || 'N/A';
            this.modalData.registroId = log.registro_id || 'N/A';
            this.modalData.anterior = formatearJSON(log.valores_anteriores);
            this.modalData.nuevo = formatearJSON(log.valores_nuevos);
            this.modalOpen = true;
        },

        // 📅 Formateador de Fechas y Horas (Convierte de YYYY-MM-DD HH:MM:SS a DD/MM/YYYY HH:MM)
        formataFechaHora(fechaRaw) {
            if (!fechaRaw) return '';
            
            // Separa YYYY-MM-DD de HH:MM:SS
            const partes = fechaRaw.split(' ');
            if (partes.length < 1) return fechaRaw;
            
            const fecha = partes[0].split('-');
            if (fecha.length !== 3) return fechaRaw;
            
            const hora = partes[1] ? partes[1].substring(0, 5) : ''; // Extrae solo HH:MM
            return `${fecha[2]}/${fecha[1]}/${fecha[0]} ${hora}`;
        }
    }
}