<?php
// Asegurar que las variables existan para evitar avisos del servidor
$equipos = $equipos ?? [];
$metricas = $metricas ?? ['total_equipos' => 0, 'disponibles' => 0, 'asignados' => 0, 'mantenimiento' => 0, 'bajas_danos' => 0];
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Activos</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1"><?= htmlspecialchars($metricas['total_equipos']) ?></h3>
        </div>
        <div class="bg-blue-50 text-blue-600 p-3 rounded-xl"><i class="ph-bold ph-desktop text-2xl"></i></div>
    </div>
    
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Disponibles</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-1"><?= htmlspecialchars($metricas['disponibles']) ?></h3>
        </div>
        <div class="bg-emerald-50 text-emerald-600 p-3 rounded-xl"><i class="ph-bold ph-check-square text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Asignados</p>
            <h3 class="text-2xl font-black text-indigo-600 mt-1"><?= htmlspecialchars($metricas['asignados']) ?></h3>
        </div>
        <div class="bg-indigo-50 text-indigo-600 p-3 rounded-xl"><i class="ph-bold ph-user text-2xl"></i></div>
    </div>

    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">En Soporte / Bajas</p>
            <h3 class="text-2xl font-black text-amber-600 mt-1"><?= htmlspecialchars($metricas['mantenimiento'] + $metricas['bajas_danos']) ?></h3>
        </div>
        <div class="bg-amber-50 text-amber-600 p-3 rounded-xl"><i class="ph-bold ph-wrench text-2xl"></i></div>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" x-data="inventarioComponent()">
    
    <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" x-model="search" placeholder="Buscar por nombre, serie/número, tipo o colaborador..." 
                   class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 bg-white transition-colors">
        </div>
        
        <div class="flex items-center gap-3">
            <select x-model="filterEstado" class="border border-slate-200 rounded-xl px-4 py-2 text-sm bg-white focus:outline-none focus:border-blue-500">
                <option value="TODOS">Todos los Estados</option>
                <option value="DISPONIBLE">Disponible</option>
                <option value="ASIGNADO">Asignado</option>
                <option value="MANTENIMIENTO">Mantenimiento</option>
                <option value="BAJA">Baja</option>
                <option value="ROBADO/PERDIDO">Robado / Perdido</option>
            </select>

            <button onclick="window.print()" class="flex items-center gap-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 px-4 py-2 rounded-xl text-sm font-bold transition-colors">
                <i class="ph ph-printer text-lg"></i>
                Imprimir / PDF
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-widest font-bold border-b border-slate-200">
                    <th class="py-4 px-6">ID / Tipo</th>
                    <th class="py-4 px-6">Activo / Modelo</th>
                    <th class="py-4 px-6">Nº Serie o Línea</th>
                    <th class="py-4 px-6">Componentes</th>
                    <th class="py-4 px-6">Custodio Actual</th>
                    <th class="py-4 px-6 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <template x-for="equipo in filteredEquipos" :key="equipo.tipo + '-' + equipo.id">
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        
                        <td class="py-4 px-6">
                            <span class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded" x-text="'#' + equipo.id"></span>
                            <p class="font-bold text-slate-700 mt-1 flex items-center gap-1.5">
                                <template x-if="equipo.tipo === 'Celular'">
                                    <i class="ph ph-phone text-blue-500"></i>
                                </template>
                                <template x-if="equipo.tipo !== 'Celular'">
                                    <i class="ph ph-desktop text-slate-500"></i>
                                </template>
                                <span x-text="equipo.tipo"></span>
                            </p>
                        </td>
                        
                        <td class="py-4 px-6">
                            <p class="font-bold text-slate-900" x-text="equipo.equipo_nombre"></p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="equipo.marca + ' ' + (equipo.modelo ?? '')"></p>
                        </td>
                        
                        <td class="py-4 px-6">
                            <span class="font-mono text-xs font-bold" 
                                  :class="equipo.tipo === 'Celular' ? 'text-blue-600 bg-blue-50 px-2 py-0.5 rounded' : 'text-slate-600'" 
                                  x-text="equipo.serie ?? 'S/N'">
                            </span>
                        </td>
                        
                        <td class="py-4 px-6">
                            <template x-if="equipo.tipo !== 'Celular'">
                                <span class="inline-flex items-center gap-1.5 text-xs text-slate-600 bg-slate-100 border border-slate-200 px-2 py-1 rounded-lg">
                                    <i class="ph ph-cpu text-sm text-blue-500"></i>
                                    <span class="font-bold" x-text="equipo.total_componentes"></span> hdw
                                </span>
                            </template>
                            <template x-if="equipo.tipo === 'Celular'">
                                <span class="text-xs text-slate-400 italic">Línea integrada</span>
                            </template>
                        </td>
                        
                        <td class="py-4 px-6">
                            <template x-if="equipo.colaborador_assigned_valid">
                                <div>
                                    <p class="font-semibold text-slate-800" x-text="equipo.colaborador_asignado"></p>
                                    <p class="text-[11px] text-blue-500 font-medium" x-text="equipo.colaborador_area"></p>
                                </div>
                            </template>
                            <template x-if="!equipo.colaborador_assigned_valid">
                                <span class="text-xs text-slate-400 italic">Sin asignar / En depósito</span>
                            </template>
                        </td>
                        
                        <td class="py-4 px-6 text-center">
                            <span :class="{
                                'bg-emerald-100 text-emerald-700 border-emerald-200': equipo.estado_id == 1,
                                'bg-indigo-100 text-indigo-700 border-indigo-200': equipo.estado_id == 2,
                                'bg-amber-100 text-amber-700 border-amber-200': equipo.estado_id == 3,
                                'bg-red-100 text-red-700 border-red-200': equipo.estado_id == 4,
                                'bg-slate-200 text-slate-700 border-slate-300': equipo.estado_id == 5,
                                'bg-rose-100 text-rose-700 border-rose-200': equipo.estado_id == 6
                            }" class="px-2.5 py-1 text-xs font-extrabold uppercase tracking-wider rounded-full border" x-text="equipo.estado_nombre">
                            </span>
                        </td>
                    </tr>
                </template>
                
                <tr x-show="filteredEquipos.length === 0" x-cloak>
                    <td colspan="6" class="py-12 text-center text-slate-400 bg-slate-50/20">
                        <i class="ph ph-folder-open text-4xl block mx-auto mb-2 text-slate-300"></i>
                        No se encontraron registros que coincidan con los criterios establecidos.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function inventarioComponent() {
    return {
        search: '',
        filterEstado: 'TODOS',
        // Procesamos los datos desde PHP agregándoles propiedades dinámicas convenientes para Alpine
        equipos: <?= json_encode(array_map(function($e) {
            $e['colaborador_assigned_valid'] = !empty($e['colaborador_asignado']);
            return $e;
        }, $equipos)) ?>,
        
        get filteredEquipos() {
            return this.equipos.filter(e => {
                const nombre = (e.equipo_nombre || '').toLowerCase();
                const serie = (e.serie || '').toLowerCase();
                const tipo = (e.tipo || '').toLowerCase();
                const custodio = (e.colaborador_asignado || '').toLowerCase();
                const busqueda = this.search.toLowerCase();

                const matchSearch = nombre.includes(busqueda) || 
                                    serie.includes(busqueda) || 
                                    tipo.includes(busqueda) || 
                                    custodio.includes(busqueda);
                                    
                // El filtro ahora limpia el string y valida correctamente strings parciales (ej: 'Robado/Perdido' coincide con 'ROBADO/PERDIDO')
                const eNombreClean = (e.estado_nombre || '').toUpperCase().trim();
                const matchEstado = this.filterEstado === 'TODOS' || eNombreClean === this.filterEstado;
                
                return matchSearch && matchEstado;
            });
        }
    }
}
</script>