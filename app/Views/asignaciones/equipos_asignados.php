<?php
$equipos = $equipos ?? [];
// Inicializamos la métrica de celulares en caso de que no venga del backend por defecto
$metricas = $metricas ?? ['total_asignados' => 0, 'colaboradores_con_activos' => 0, 'total_laptops' => 0, 'total_desktops' => 0];
$totalCelulares = $metricas['total_celulares'] ?? count(array_filter($equipos, function($e) { return $e['equipo_tipo'] === 'Celular'; }));
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Activos en Uso</p>
            <h3 class="text-2xl font-black text-indigo-600 mt-1"><?= htmlspecialchars($metricas['total_asignados']) ?></h3>
        </div>
        <div class="bg-indigo-50 text-indigo-600 p-3 rounded-xl"><i class="ph-bold ph-hand-holding-out text-2xl"></i></div>
    </div>
    
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Custodios Activos</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1"><?= htmlspecialchars($metricas['colaboradores_con_activos']) ?></h3>
        </div>
        <div class="bg-slate-100 text-slate-600 p-3 rounded-xl"><i class="ph-bold ph-users-three text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Laptops Asignadas</p>
            <h3 class="text-2xl font-black text-sky-600 mt-1"><?= htmlspecialchars($metricas['total_laptops']) ?></h3>
        </div>
        <div class="bg-sky-50 text-sky-600 p-3 rounded-xl"><i class="ph-bold ph-laptop text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Desktops Activas</p>
            <h3 class="text-2xl font-black text-purple-600 mt-1"><?= htmlspecialchars($metricas['total_desktops']) ?></h3>
        </div>
        <div class="bg-purple-50 text-purple-600 p-3 rounded-xl"><i class="ph-bold ph-monitor text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Líneas Móviles</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-1"><?= htmlspecialchars($totalCelulares) ?></h3>
        </div>
        <div class="bg-emerald-50 text-emerald-600 p-3 rounded-xl"><i class="ph-bold ph-device-mobile text-2xl"></i></div>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" x-data="reporteAsignadosComponent()">
    
    <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" x-model="search" placeholder="Buscar por custodio, serie, operadora, plan, imei o acta..." 
                   class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 bg-white transition-colors">
        </div>
        
        <div class="flex items-center gap-3">
            <select x-model="filterArea" class="border border-slate-200 rounded-xl px-4 py-2 text-sm bg-white focus:outline-none focus:border-blue-500">
                <option value="TODAS">Todas las Áreas</option>
                <?php 
                $areas = array_unique(array_filter(array_column($equipos, 'colaborador_area')));
                foreach($areas as $area): ?>
                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                <?php endforeach; ?>
            </select>

            <button onclick="window.print()" class="flex items-center gap-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 px-4 py-2 rounded-xl text-sm font-bold transition-colors">
                <i class="ph ph-printer text-lg"></i>
                Exportar Reporte
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-widest font-bold border-b border-slate-200">
                    <th class="py-4 px-6">Activo / Características</th>
                    <th class="py-4 px-6">Identificadores / Identidad</th>
                    <th class="py-4 px-6">Custodio Directo</th>
                    <th class="py-4 px-6">Ubicación Organizacional</th>
                    <th class="py-4 px-6">Acta de Entrega</th>
                    <th class="py-4 px-6 text-center">Estado de Entrega</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <template x-for="item in filteredItems" :key="item.equipo_id + '-' + item.equipo_tipo">
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2">
                                <template x-if="item.equipo_tipo === 'Celular'">
                                    <div class="text-emerald-500 bg-emerald-50 p-1.5 rounded-lg"><i class="ph-bold ph-phone-call text-base"></i></div>
                                </template>
                                <template x-if="item.equipo_tipo !== 'Celular'">
                                    <div class="text-indigo-500 bg-indigo-50 p-1.5 rounded-lg"><i class="ph-bold ph-desktop text-base"></i></div>
                                </template>
                                <div>
                                    <p class="font-bold text-slate-900" x-text="item.equipo_nombre"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="item.equipo_tipo + ' • ' + item.equipo_marca + ' ' + (item.equipo_modelo || '')"></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-[11px] font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded block w-fit" 
                                  x-text="item.equipo_tipo === 'Celular' ? 'TELEFONÍA' : 'HARDWARE ID: #' + item.equipo_id"></span>
                            <p class="text-xs font-mono text-slate-700 mt-1" x-text="item.equipo_tipo === 'Celular' ? 'Línea: ' + item.equipo_serie : 'S/N: ' + (item.equipo_serie || 'N/A')"></p>
                        </td>
                        <td class="py-4 px-6">
                            <p class="font-bold text-slate-800" x-text="item.colaborador_nombre"></p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="item.colaborador_correo || 'Sin correo registrado'"></p>
                        </td>
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-700" x-text="item.colaborador_area || 'Sin Área'"></p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="item.colaborador_cargo || 'Sin Cargo'"></p>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-xl">
                                <i class="ph ph-file-text"></i>
                                <span x-text="item.codigo_acta"></span>
                            </span>
                            <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase" x-text="'Entregado el: ' + formataFecha(item.fecha_entrega)"></p>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-slate-50 text-slate-700 border-slate-200"
                                  :title="item.observacion_item" 
                                  x-text="item.estado_entrega_equipo || 'Bueno'">
                            </span>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredItems.length === 0" x-cloak>
                    <td colspan="6" class="py-12 text-center text-slate-400 bg-slate-50/20">
                        <i class="ph ph-user-focus text-4xl block mx-auto mb-2 text-slate-300"></i>
                        No se identificaron asignaciones vigentes bajo esos parámetros.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function reporteAsignadosComponent() {
    return {
        search: '',
        filterArea: 'TODAS',
        items: <?= json_encode($equipos) ?>,
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
        formataFecha(fechaRaw) {
            if(!fechaRaw) return '';
            const partes = fechaRaw.split('-');
            if(partes.length !== 3) return fechaRaw;
            return `${partes[2]}/${partes[1]}/${partes[0]}`; // Convierte de YYYY-MM-DD a DD/MM/YYYY
        }
    }
}
</script>