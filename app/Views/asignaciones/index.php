<?php
$actas = $actas ?? [];
$colaboradores = $colaboradores ?? [];
$equipos = $equipos ?? []; 
$celularesDisponibles = $celularesDisponibles ?? []; 

$canCreateAsignaciones = sessionHasPermission('asignaciones', 'crear');
$canEditAsignaciones = sessionHasPermission('asignaciones', 'editar');
$canDeleteAsignaciones = sessionHasPermission('asignaciones', 'eliminar');
$canViewAsignaciones = sessionHasPermission('asignaciones', 'ver');
?>

<div class="col-span-3">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Actas de Asignación</h2>
            <p class="text-slate-500 mt-1">Gestión transaccional de entrega, custodia y lotes de activos informáticos y telefonía</p>
        </div>
        <?php if ($canCreateAsignaciones): ?>
        <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-emerald-200 transition-all flex items-center gap-2">
            <i class="ph ph-file-plus text-xl"></i> Nueva Asignación en Lote
        </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'guardado'): ?>
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-check-circle text-lg"></i> ¡El Acta de asignación y sus activos se guardaron con éxito!
            </div>
        <?php elseif ($_GET['msg'] === 'sin_activos'): ?> 
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-warning text-lg"></i> Debe seleccionar al menos un equipo informático o un celular corporativo para procesar el acta.
            </div>
        <?php elseif ($_GET['msg'] === 'sin_equipos'): ?>
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-warning text-lg"></i> Debe seleccionar al menos un equipo informático para procesar el acta.
            </div>
        <?php elseif ($_GET['msg'] === 'eliminado'): ?>
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-info text-lg"></i> El acta ha sido anulada y los activos volvieron a estar Disponibles.
            </div>
        <?php elseif ($_GET['msg'] === 'error'): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-x-circle text-lg"></i> Ocurrió un fallo interno en la operación transaccional.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-slate-50 text-slate-500 rounded-xl border border-slate-100">
                <i class="ph ph-sliders text-xl"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Filtros Avanzados</span>
                <span class="text-sm font-medium text-slate-600">Restringir registros de la tabla</span>
            </div>
        </div>

        <form method="GET" action="/asignaciones" id="formFiltroColaborador" class="w-full sm:w-80">
            <div class="relative">
                <select 
                    name="colaborador_id" 
                    onchange="document.getElementById('formFiltroColaborador').submit();"
                    class="w-full p-3 pl-10 pr-10 bg-slate-50 border border-slate-200 rounded-2xl appearance-none focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-700 text-sm font-semibold transition cursor-pointer shadow-sm">
                    
                    <option value="">👥 Todos los colaboradores</option>
                    
                    <?php foreach ($colaboradores as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= isset($_GET['colaborador_id']) && (int)$_GET['colaborador_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombres']) ?> (<?= htmlspecialchars($c['area'] ?? 'Sin área') ?>)
                        </option>
                    <?php endforeach; ?>
                    
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <i class="ph ph-user-list text-lg"></i>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                    <i class="ph ph-caret-down text-sm"></i>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="p-4">Código Acta</th>
                        <th class="p-4">Colaborador / Custodio</th>
                        <th class="p-4">Activos Vinculados</th>
                        <th class="p-4">Componentes</th>
                        <th class="p-4">Fecha Entrega</th>
                        <th class="p-4">Estado Acta</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($actas) > 0): ?>
                        <?php foreach ($actas as $a): ?>
                        <tr class="hover:bg-slate-50 transition text-sm text-slate-700">
                            <td class="p-4 font-mono font-bold text-emerald-600">
                                <?= htmlspecialchars($a['codigo_acta']) ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 font-bold text-slate-800">
                                    <i class="ph ph-user text-slate-400 text-lg"></i>
                                    <?= htmlspecialchars($a['colaborador_nombre']) ?>
                                </div>
                                <div class="text-xs text-slate-400 pl-6">
                                    <?= htmlspecialchars($a['colaborador_area'] ?? 'General') ?> — <?= htmlspecialchars($a['colaborador_cargo'] ?? 'N/A') ?>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col gap-1">
                                    <?php if ((int)$a['total_equipos'] > 0): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md w-fit">
                                            <i class="ph ph-desktop text-slate-500"></i> <?= $a['total_equipos'] ?> Hardware
                                        </span>
                                    <?php endif; ?>
                                    <?php if ((int)$a['total_celulares'] > 0): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md w-fit">
                                            <i class="ph ph-device-mobile text-blue-500"></i> <?= $a['total_celulares'] ?> Telefonía
                                        </span>
                                    <?php endif; ?>
                                    <?php if ((int)$a['total_equipos'] === 0 && (int)$a['total_celulares'] === 0): ?>
                                        <span class="text-xs text-slate-400 italic">Sin ítems</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="p-4 text-slate-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <i class="ph ph-cpu text-slate-400 text-lg"></i>
                                    <?php if ((int)$a['total_componentes'] > 0): ?>
                                        <span class="bg-slate-100 text-slate-800 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                            <?= $a['total_componentes'] ?> Internos
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs italic">Ninguno</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="p-4 text-slate-500">
                                <div class="flex items-center gap-1">
                                    <i class="ph ph-calendar text-slate-400"></i>
                                    <?= htmlspecialchars($a['fecha_entrega']) ?>
                                </div>
                            </td>
                            <td class="p-4">
                                <?php 
                                    $estado = $a['estado_acta'];
                                    if ($estado === 'Vigente') {
                                        $badgeClass = 'bg-emerald-50 text-emerald-600 border border-emerald-200';
                                    } elseif ($estado === 'Parcial') {
                                        $badgeClass = 'bg-amber-50 text-amber-600 border border-amber-200';
                                    } else {
                                        $badgeClass = 'bg-slate-100 text-slate-500 border border-slate-200';
                                    }
                                ?>
                                <span class="<?= $badgeClass ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                    <?= htmlspecialchars($estado) ?>
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1.5">
                                    <?php if ($canViewAsignaciones): ?>
                                    <a href="/asignaciones/ver?id=<?= $a['id'] ?>" title="Ver equipos asignados y devoluciones" class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <i class="ph ph-eye text-lg"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteAsignaciones): ?>
                                    <a href="/asignaciones/eliminar?id=<?= $a['id'] ?>" onclick="return confirm('¿Está seguro de eliminar esta acta? Todos los equipos y celulares vinculados volverán automáticamente a estar DISPONIBLES.')" title="Anular Acta" class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                        <i class="ph ph-trash text-lg"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="p-10 text-center text-slate-400">No hay actas de asignación registradas para los criterios seleccionados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalAsig" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8 animate-fade-in overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Registrar Acta de Entrega</h3>
                <p class="text-xs text-slate-400">Entrega uno o varios activos bajo un mismo código correlativo</p>
            </div>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl transition-colors">
                <i class="ph ph-x"></i>
            </button>
        </div>
        
        <form action="/asignaciones/guardar" method="POST" class="space-y-4">
            <div>
                <label class="text-sm font-bold text-slate-700">Colaborador / Custodio Responsable</label>
                <select name="colaborador_id" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 text-sm">
                    <option value="" disabled selected>Seleccione el empleado</option>
                    <?php foreach($colaboradores as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombres']) ?> (<?= htmlspecialchars($c['cargo'] ?? 'Sin cargo') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="text-sm font-bold text-slate-700">Fecha de Entrega Oficial</label>
                <input type="date" name="fecha_entrega" value="<?= date('Y-m-d') ?>" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 text-sm">
            </div>

            <div>
                <label class="text-sm font-bold text-slate-700 block mb-2">Seleccione los Equipos a Entregar (Solo Disponibles)</label>
                <div class="border border-slate-100 rounded-2xl bg-slate-50 p-4 max-h-44 overflow-y-auto space-y-3">
                    <?php if (!empty($equipos)): ?>
                        <?php foreach($equipos as $e): ?>
                            <div class="bg-white p-3 rounded-xl border border-slate-200 flex flex-col gap-2 shadow-sm">
                                <label class="flex items-start gap-3 cursor-pointer select-none text-sm text-slate-700 font-medium">
                                    <input type="checkbox" name="equipos_seleccionados[]" value="<?= $e['id'] ?>" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" onchange="toggleDetalleEquipo(this, <?= $e['id'] ?>)">
                                    <div>
                                        <span class="font-bold text-slate-800">[<?= htmlspecialchars($e['tipo']) ?>]</span> <?= htmlspecialchars($e['nombre']) ?>
                                        <span class="block text-xs font-mono text-slate-400">S/N: <?= htmlspecialchars($e['serie']) ?> | <?= htmlspecialchars($e['marca'] ?? 'Genérico') ?></span>
                                    </div>
                                </label>
                                
                                <div id="detalles_equipo_<?= $e['id'] ?>" class="hidden pl-6 pt-1 border-t border-dashed border-slate-100 space-y-2">
                                    <div class="flex gap-2">
                                        <div class="w-1/3">
                                            <label class="text-[11px] font-bold text-slate-500 uppercase">Estado Entrega</label>
                                            <select name="estado_entrega_<?= $e['id'] ?>" class="w-full p-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs focus:outline-none">
                                                <option value="Bueno" selected>Bueno</option>
                                                <option value="Regular">Regular</option>
                                                <option value="Malo">Malo</option>
                                            </select>
                                        </div>
                                        <div class="w-2/3">
                                            <label class="text-[11px] font-bold text-slate-500 uppercase">Observación del Ítem</label>
                                            <input type="text" name="observacion_<?= $e['id'] ?>" placeholder="Ej: Cargador rayado..." class="w-full p-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 text-center py-4">No hay equipos en bodega con estado 'DISPONIBLE'.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="text-sm font-bold text-slate-700 block mb-2">Seleccione Celulares o Líneas a Entregar</label>
                <div class="border border-slate-100 rounded-2xl bg-slate-50 p-4 max-h-44 overflow-y-auto space-y-3">
                    <?php if(!empty($celularesDisponibles)): ?>
                        <?php foreach($celularesDisponibles as $cel): ?>
                            <div class="bg-white p-3 rounded-xl border border-slate-200 flex flex-col gap-2 shadow-sm">
                                <label class="flex items-start gap-3 cursor-pointer select-none text-sm text-slate-700 font-medium">
                                    <input type="checkbox" name="celulares_seleccionados[]" value="<?= $cel['id'] ?>" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" onchange="toggleDetalleCelular(this, <?= $cel['id'] ?>)">
                                    <div>
                                        <span class="font-bold text-slate-800">📱 <?= htmlspecialchars($cel['numero_celular']) ?></span> — <?= htmlspecialchars($cel['celular_marca'] ?? 'Sin Terminal') ?> <?= htmlspecialchars($cel['celular_modelo'] ?? '') ?>
                                        <span class="block text-xs font-mono text-slate-400">Plan: <?= htmlspecialchars($cel['nombre_plan']) ?> | Op: <?= htmlspecialchars($cel['operador']) ?></span>
                                    </div>
                                </label>
                                
                                <div id="detalles_cel_<?= $cel['id'] ?>" class="hidden pl-6 pt-1 border-t border-dashed border-slate-100 space-y-2">
                                    <div class="flex gap-2">
                                        <div class="w-1/3">
                                            <label class="text-[11px] font-bold text-slate-500 uppercase">Estado Entrega</label>
                                            <select name="estado_entrega_cel_<?= $cel['id'] ?>" class="w-full p-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs focus:outline-none">
                                                <option value="Bueno" selected>Bueno</option>
                                                <option value="Excelente">Excelente</option>
                                                <option value="Regular">Regular</option>
                                            </select>
                                        </div>
                                        <div class="w-2/3">
                                            <label class="text-[11px] font-bold text-slate-500 uppercase">Observación del Teléfono</label>
                                            <input type="text" name="observacion_cel_<?= $cel['id'] ?>" placeholder="Ej: Con mica de vidrio colocada..." class="w-full p-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 text-center py-4">No hay líneas móviles ni celulares disponibles en inventario.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <label class="text-sm font-bold text-slate-700">Observaciones Generales del Acta</label>
                <textarea name="observacion_general" rows="2" placeholder="Notas sobre las condiciones generales de la firma del acta..." class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 text-sm"></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">Generar Acta</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() { document.getElementById('modalAsig').classList.remove('hidden'); }
function closeModal() { document.getElementById('modalAsig').classList.add('hidden'); }

function toggleDetalleEquipo(checkbox, equipoId) {
    const contenedorDetalle = document.getElementById('detalles_equipo_' + equipoId);
    contenedorDetalle.classList.toggle('hidden', !checkbox.checked);
}

function toggleDetalleCelular(checkbox, planId) {
    const contenedorDetalle = document.getElementById('detalles_cel_' + planId);
    contenedorDetalle.classList.toggle('hidden', !checkbox.checked);
}
</script>