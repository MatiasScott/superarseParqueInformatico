<?php
// Mapeo seguro de los datos que vienen desde el AsignacionController
$acta = $acta ?? [];
$detalles = $acta['equipos'] ?? [];
$celulares = $acta['celulares'] ?? []; // 📱 Capturamos los celulares del arreglo global
$componentes = $acta['componentes'] ?? []; // 🔧 Capturamos los componentes periféricos/partes del arreglo global

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$permisoRecibir = !empty($_SESSION['rol']) && $_SESSION['rol'] === 'admin'
    ? true
    : !empty($_SESSION['permisos']['asignaciones']['recibir']);
?>

<div class="col-span-3">
    <div class="mb-6">
        <a href="/asignaciones" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors text-sm font-bold mb-2">
            <i class="ph ph-arrow-left text-lg"></i> Regresar al listado
        </a>
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-3xl font-bold text-slate-800">Control de Acta</h2>
                    <span class="font-mono bg-emerald-50 text-emerald-700 px-3 py-1 text-sm font-bold border border-emerald-200 rounded-xl">
                        <?= htmlspecialchars($acta['codigo_acta']) ?>
                    </span>
                </div>
                <p class="text-slate-500 mt-1">Monitoreo de ítems entregados y registro de devoluciones parciales o totales</p>
            </div>
            
            <div>
                <a href="/asignaciones/imprimirActa?id=<?= $acta['id'] ?>" target="_blank" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-amber-200 transition-all flex items-center justify-center gap-2 text-sm">
                    <i class="ph ph-printer text-xl"></i> Imprimir Formato Oficial
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'devuelto'): ?>
            <div class="mb-4 p-4 bg-orange-50 border border-orange-200 text-orange-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-arrow-counter-clockwise text-lg"></i> ¡El activo se ha reincorporado al inventario en estado 'Disponible' correctamente!
            </div>
        <?php elseif ($_GET['msg'] === 'error'): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-x-circle text-lg"></i> Ocurrió un error interno al procesar el cambio de estado.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">Información del Acta</h3>
            
            <div>
                <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Custodio / Colaborador</span>
                <p class="text-slate-800 font-bold flex items-center gap-1 mt-0.5">
                    <i class="ph ph-user text-slate-400"></i> <?= htmlspecialchars($acta['colaborador_nombre']) ?>
                </p>
                <p class="text-xs text-slate-500 pl-5"><?= htmlspecialchars($acta['colaborador_area']) ?> — <?= htmlspecialchars($acta['colaborador_cargo']) ?></p>
            </div>

            <div>
                <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Técnico que Entrega</span>
                <p class="text-slate-700 font-medium mt-0.5">
                    <i class="ph ph-user-gear text-slate-400"></i> <?= htmlspecialchars($acta['usuario_technical'] ?? $acta['usuario_tecnico']) ?>
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Fecha Entrega</span>
                    <p class="text-slate-700 text-sm font-medium"><?= $acta['fecha_entrega'] ?></p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Fecha Dev. Global</span>
                    <p class="text-slate-700 text-sm font-medium"><?= $acta['fecha_devolucion_global'] ?? '—' ?></p>
                </div>
            </div>

            <div>
                <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Estado de Cobertura</span>
                <?php 
                    $est = $acta['estado_acta'];
                    $badge = $est === 'Vigente' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : ($est === 'Parcial' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-slate-100 text-slate-500 border-slate-200');
                ?>
                <span class="<?= $badge ?> px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border inline-block mt-1">
                    <?= htmlspecialchars($est) ?>
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Observación General</span>
                <p class="text-xs text-slate-600 italic bg-slate-50 p-3 rounded-xl border border-slate-100 mt-1">
                    <?= htmlspecialchars($acta['observacion_general'] ?? 'Sin observaciones iniciales en la cabecera.') ?>
                </p>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            <?php if (!empty($detalles)): ?>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-desktop text-slate-400"></i> Equipos Informáticos Vinculados
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                                <th class="p-4">Equipo Informático</th>
                                <th class="p-4">Estado Entrega</th>
                                <th class="p-4">Situación Ítem</th>
                                <th class="p-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($detalles as $eq): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800">[<?= htmlspecialchars($eq['tipo']) ?>] <?= htmlspecialchars($eq['nombre']) ?></div>
                                        <div class="text-xs text-slate-400 font-mono">S/N: <?= htmlspecialchars($eq['serie']) ?> | Marca: <?= htmlspecialchars($eq['marca']) ?></div>
                                        <?php if(!empty($eq['observacion_item'])): ?>
                                            <div class="text-[11px] text-amber-600 bg-amber-50/50 inline-block px-2 py-0.5 rounded mt-1 max-w-xs truncate" title="<?= htmlspecialchars($eq['observacion_item']) ?>">
                                                <strong>Historial:</strong> <?= htmlspecialchars($eq['observacion_item']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-slate-600 font-medium">
                                        <?= htmlspecialchars($eq['estado_entrega_equipo']) ?>
                                    </td>
                                    <td class="p-4">
                                        <?php 
                                            $enUso = $eq['estado_item'] === 'En Uso';
                                            $itemBadge = $enUso ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-600';
                                        ?>
                                        <span class="<?= $itemBadge ?> px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider">
                                            <?= htmlspecialchars($eq['estado_item']) ?>
                                        </span>
                                        <?php if(!$enUso && !empty($eq['fecha_devolucion_equipo'])): ?>
                                            <span class="block text-[11px] text-slate-400 mt-0.5">Retorno: <?= $eq['fecha_devolucion_equipo'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($enUso): ?>
                                            <?php if ($permisoRecibir): ?>
                                                <button type="button" onclick="openDevolucionModal(<?= $eq['id'] ?>, '<?= htmlspecialchars($eq['nombre']) ?> (<?= htmlspecialchars($eq['serie']) ?>)', 'equipo')" class="px-3 py-1.5 bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto">
                                                    <i class="ph ph-arrow-counter-clockwise"></i> Recibir
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs italic"><i class="ph ph-lock-key text-lg text-amber-500 vertical-middle"></i> Sin permiso</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs italic"><i class="ph ph-check-square text-lg text-emerald-500 vertical-middle"></i> Procesado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($celulares)): ?>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-device-mobile text-blue-500"></i> Líneas y Telefonía Vinculada
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                                <th class="p-4">Dispositivo / Número</th>
                                <th class="p-4">Plan / Operador</th>
                                <th class="p-4">Estado Entrega</th>
                                <th class="p-4">Situación</th>
                                <th class="p-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($celulares as $cel): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800">📱 <?= htmlspecialchars($cel['numero_celular']) ?></div>
                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($cel['celular_marca']) ?> <?= htmlspecialchars($cel['celular_modelo']) ?></div>
                                        <div class="text-[10px] font-mono text-slate-400">IMEI: <?= htmlspecialchars($cel['imei'] ?? 'N/A') ?></div>
                                    </td>
                                    <td class="p-4 text-xs">
                                        <span class="font-semibold text-slate-700 block"><?= htmlspecialchars($cel['nombre_plan']) ?></span>
                                        <span class="text-slate-400 font-mono"><?= htmlspecialchars($cel['operador']) ?></span>
                                    </td>
                                    <td class="p-4 text-slate-600 font-medium text-xs">
                                        <?= htmlspecialchars($cel['estado_entrega']) ?>
                                    </td>
                                    <td class="p-4">
                                        <?php 
                                            $celEnUso = $cel['estado_item'] === 'En Uso';
                                            $celBadge = $celEnUso ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-600';
                                        ?>
                                        <span class="<?= $celBadge ?> px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider">
                                            <?= htmlspecialchars($cel['estado_item']) ?>
                                        </span>
                                        <?php if(!$celEnUso && !empty($cel['fecha_devolucion'])): ?>
                                            <span class="block text-[11px] text-slate-400 mt-0.5">Retorno: <?= $cel['fecha_devolucion'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($celEnUso): ?>
                                            <?php if ($permisoRecibir): ?>
                                                <button type="button" onclick="openDevolucionModal(<?= $cel['id'] ?>, 'Celular: <?= htmlspecialchars($cel['numero_celular']) ?>', 'celular')" class="px-3 py-1.5 bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto">
                                                    <i class="ph ph-arrow-counter-clockwise"></i> Recibir
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs italic"><i class="ph ph-lock-key text-lg text-amber-500 vertical-middle"></i> Sin permiso</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs italic"><i class="ph ph-check-square text-lg text-emerald-500 vertical-middle"></i> Procesado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($componentes)): ?>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-wrench text-purple-500"></i> Componentes y Periféricos
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                                <th class="p-4">Componente</th>
                                <th class="p-4">Equipo Padre</th>
                                <th class="p-4">Estado Entrega</th>
                                <th class="p-4">Situación</th>
                                <th class="p-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($componentes as $comp): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800">🔧 <?= htmlspecialchars($comp['nombre']) ?></div>
                                        <div class="text-xs text-slate-400 font-mono">S/N: <?= htmlspecialchars($comp['serie'] ?? 'N/A') ?> | Marca: <?= htmlspecialchars($comp['marca'] ?? 'N/A') ?></div>
                                    </td>
                                    <td class="p-4 text-xs text-slate-600 font-medium">
                                        <?= htmlspecialchars($comp['equipo_padre'] ?? 'Asignación Directa') ?>
                                    </td>
                                    <td class="p-4 text-slate-600 font-medium text-xs">
                                        <?= htmlspecialchars($comp['estado_entrega'] ?? 'Buen Estado') ?>
                                    </td>
                                    <td class="p-4">
                                        <?php 
                                            $compEnUso = ($comp['estado_item'] ?? 'En Uso') === 'En Uso';
                                            $compBadge = $compEnUso ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-600';
                                        ?>
                                        <span class="<?= $compBadge ?> px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider">
                                            <?= htmlspecialchars($comp['estado_item'] ?? 'En Uso') ?>
                                        </span>
                                        <?php if(!$compEnUso && !empty($comp['fecha_devolucion'])): ?>
                                            <span class="block text-[11px] text-slate-400 mt-0.5">Retorno: <?= $comp['fecha_devolucion'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($compEnUso): ?>
                                            <?php if ($permisoRecibir): ?>
                                                <button type="button" onclick="openDevolucionModal(<?= $comp['id'] ?>, 'Componente: <?= htmlspecialchars($comp['nombre']) ?>', 'componente')" class="px-3 py-1.5 bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto">
                                                    <i class="ph ph-arrow-counter-clockwise"></i> Recibir
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs italic"><i class="ph ph-lock-key text-lg text-amber-500 vertical-middle"></i> Sin permiso</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs italic"><i class="ph ph-check-square text-lg text-emerald-500 vertical-middle"></i> Procesado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<div id="modalDevolver" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Registrar Devolución</h3>
                <p class="text-xs text-slate-400 mt-0.5">El activo reingresará de inmediato al Almacén General</p>
            </div>
            <button onclick="closeDevolucionModal()" class="text-slate-400 hover:text-red-500 text-xl transition-colors">
                <i class="ph ph-x"></i>
            </button>
        </div>
        
        <form id="formDevolucionDinamico" action="" method="POST" class="space-y-4">
            <input type="hidden" name="detalle_id" id="modal_detalle_id">
            <input type="hidden" name="acta_id" value="<?= $acta['id'] ?>">

            <div>
                <label class="text-xs text-slate-400 block font-bold uppercase tracking-wider">Activo Seleccionado</label>
                <p id="modal_equipo_nombre" class="text-sm font-bold text-slate-800 mt-0.5 bg-slate-50 p-2.5 rounded-lg border border-slate-100 font-mono"></p>
            </div>

            <div>
                <label class="text-sm font-bold text-slate-700">Condición de Recepción Técnica</label>
                <textarea name="observacion_devolucion" required rows="3" placeholder="Describa el estado en el que se recibe el activo..." class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-orange-500 text-sm"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeDevolucionModal()" class="flex-1 py-2.5 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-colors text-sm">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-100 hover:bg-orange-700 transition-all text-sm">Confirmar Recepción</button>
            </div>
        </form>
    </div>
</div>

<script>
// Manejo dinámico de rutas en el modal añadiendo el tipo 'componente'
function openDevolucionModal(detalleId, equipoNombre, tipo) {
    document.getElementById('modal_detalle_id').value = detalleId;
    document.getElementById('modal_equipo_nombre').innerText = equipoNombre;
    
    const formulario = document.getElementById('formDevolucionDinamico');
    if (tipo === 'celular') {
        formulario.action = "/asignaciones/devolverCelular";
    } else if (tipo === 'componente') {
        formulario.action = "/asignaciones/devolverComponente";
    } else {
        formulario.action = "/asignaciones/devolverEquipo";
    }
    
    document.getElementById('modalDevolver').classList.remove('hidden');
}

function closeDevolucionModal() {
    document.getElementById('modalDevolver').classList.add('hidden');
}
</script>