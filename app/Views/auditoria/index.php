<?php
$logs = $logs ?? [];
$metricas = $metricas ?? ['total_eventos' => 0, 'total_inserts' => 0, 'total_updates' => 0, 'total_deletes' => 0];
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Operaciones Totales</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1"><?= htmlspecialchars($metricas['total_eventos']) ?></h3>
        </div>
        <div class="bg-slate-100 text-slate-600 p-3 rounded-xl"><i class="ph-bold ph-fingerprint text-2xl"></i></div>
    </div>
    
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inserciones (INSERT)</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-1"><?= htmlspecialchars($metricas['total_inserts']) ?></h3>
        </div>
        <div class="bg-emerald-50 text-emerald-600 p-3 rounded-xl"><i class="ph-bold ph-plus-circle text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Modificaciones (UPDATE)</p>
            <h3 class="text-2xl font-black text-blue-600 mt-1"><?= htmlspecialchars($metricas['total_updates']) ?></h3>
        </div>
        <div class="bg-blue-50 text-blue-600 p-3 rounded-xl"><i class="ph-bold ph-pencil-circle text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Eliminaciones (DELETE)</p>
            <h3 class="text-2xl font-black text-rose-600 mt-1"><?= htmlspecialchars($metricas['total_deletes']) ?></h3>
        </div>
        <div class="bg-rose-50 text-rose-600 p-3 rounded-xl"><i class="ph-bold ph-trash-simple text-2xl"></i></div>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" 
     x-data="bitacoraAuditoriaComponent()">
    
    <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" x-model="search" placeholder="Buscar por tabla, ID registro, usuario o IP..." 
                   class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 bg-white transition-colors">
        </div>
        
        <div class="flex items-center gap-3">
            <select x-model="filterAccion" class="border border-slate-200 rounded-xl px-4 py-2 text-sm bg-white focus:outline-none focus:border-blue-500">
                <option value="TODOS">Todas las Acciones</option>
                <option value="INSERT">INSERT</option>
                <option value="UPDATE">UPDATE</option>
                <option value="DELETE">DELETE</option>
                <option value="LOGIN">LOGIN</option>
                <option value="LOGOUT">LOGOUT</option>
            </select>

            <button onclick="window.print()" class="flex items-center gap-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 px-4 py-2 rounded-xl text-sm font-bold transition-colors">
                <i class="ph ph-printer text-lg"></i>
                Imprimir Historial
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-widest font-bold border-b border-slate-200">
                    <th class="py-4 px-6">Fecha / Evento</th>
                    <th class="py-4 px-6 text-center">Acción</th>
                    <th class="py-4 px-6">Ubicación del Cambio</th>
                    <th class="py-4 px-6">Operador</th>
                    <th class="py-4 px-6">Dirección IP</th>
                    <th class="py-4 px-6 text-center">Detalle JSON</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <template x-for="log in filteredLogs" :key="log.id">
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-bold text-slate-900" x-text="formataFechaHora(log.fecha_evento)"></p>
                            <p class="text-[11px] font-mono text-slate-400 mt-0.5" x-text="'LOG ID: #' + log.id"></p>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span :class="{
                                'bg-emerald-100 text-emerald-700 border-emerald-200': log.accion === 'INSERT',
                                'bg-blue-100 text-blue-700 border-blue-200': log.accion === 'UPDATE',
                                'bg-rose-100 text-rose-700 border-rose-200': log.accion === 'DELETE',
                                'bg-purple-100 text-purple-700 border-purple-200': log.accion === 'LOGIN' || log.accion === 'LOGOUT'
                            }" class="px-2.5 py-1 text-xs font-black tracking-wide rounded-md border" x-text="log.accion">
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-700" x-text="log.tabla_afectada || 'N/A'"></p>
                            <p class="text-xs font-mono text-slate-400 mt-0.5" x-text="log.registro_id ? 'Registro ID: #' + log.registro_id : 'Sin ID específico'"></p>
                        </td>
                        <td class="py-4 px-6">
                            <p class="font-bold text-slate-800" x-text="log.usuario_nombre || 'Sistema (Automático)'"></p>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider" x-text="log.usuario_rol || ''"></p>
                        </td>
                        <td class="py-4 px-6 font-mono text-xs text-slate-600" x-text="log.ip_origen || '127.0.0.1'"></td>
                        <td class="py-4 px-6 text-center">
                            <button @click="abrirDetalle(log)" 
                                    class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-100 px-3 py-1.5 rounded-xl text-xs font-bold transition-all">
                                <i class="ph-bold ph-eye mr-1"></i> Inspeccionar
                            </button>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredLogs.length === 0" x-cloak>
                    <td colspan="6" class="py-12 text-center text-slate-400 bg-slate-50/20">
                        <i class="ph ph-fingerprint-slash text-4xl block mx-auto mb-2 text-slate-300"></i>
                        No se detectaron eventos registrados bajo los filtros seleccionados.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" 
         x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false">
        
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden"
             @click.away="modalOpen = false">
            
            <div class="p-6 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <div>
                    <h4 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="ph ph-brackets-curly text-blue-600"></i> Auditoría Forense de Datos
                    </h4>
                    <p class="text-xs text-slate-400 mt-1" x-text="'Tabla: ' + modalData.tabla + ' | Registro ID: #' + modalData.registroId"></p>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph-bold ph-x text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-900 text-slate-200 font-mono text-xs selection:bg-blue-500">
                <div>
                    <span class="text-rose-400 font-bold uppercase tracking-wider block mb-2">// Valores Anteriores (Pre-cambio)</span>
                    <pre class="bg-slate-950 p-4 rounded-xl border border-slate-800 overflow-x-auto whitespace-pre-wrap min-h-[150px]" 
                         x-text="modalData.anterior"></pre>
                </div>
                <div>
                    <span class="text-emerald-400 font-bold uppercase tracking-wider block mb-2">// Valores Nuevos (Post-cambio)</span>
                    <pre class="bg-slate-950 p-4 rounded-xl border border-slate-800 overflow-x-auto whitespace-pre-wrap min-h-[150px]" 
                         x-text="modalData.nuevo"></pre>
                </div>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50 text-right">
                <button @click="modalOpen = false" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-2 rounded-xl text-xs transition-colors">
                    Cerrar Auditoría
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.DATA_AUDITORIA_LOGS = <?= json_encode($logs ?? []) ?>;
</script>

<script src="/js/auditoria.js"></script>