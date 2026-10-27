<?php
$componentes = $componentes ?? [];
$totalComponentes = $totalComponentes ?? 0;
$componentesBaja = $componentesBaja ?? 0;
$equipos = $equipos ?? []; 

$canCreateComponentes = sessionHasPermission('componentes', 'crear');
$canEditComponentes = sessionHasPermission('componentes', 'editar');
$canDeleteComponentes = sessionHasPermission('componentes', 'eliminar');
?>

<div class="col-span-3 space-y-6 animate-fade-in">

    <?php if (isset($_GET['msg'])): ?>
        <div id="toast-alert" class="fixed top-5 right-5 z-50 flex items-center p-4 mb-4 text-slate-800 bg-white rounded-2xl shadow-xl border border-slate-100 max-w-sm transition-all animate-fade-in" role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-xl <?php
                echo match($_GET['msg']) {
                    'guardado'   => 'bg-green-50 text-green-500',
                    'actualizado'=> 'bg-blue-50 text-blue-500',
                    'devuelto'   => 'bg-emerald-50 text-emerald-500',
                    'eliminado'  => 'bg-red-50 text-red-500',
                    default      => 'bg-slate-50 text-slate-500'
                };
            ?>">
                <i class="ph-bold <?php
                    echo match($_GET['msg']) {
                        'guardado'   => 'ph-check-circle',
                        'actualizado'=> 'ph-arrows-counter-clockwise',
                        'devuelto'   => 'ph-arrow-u-up-left',
                        'eliminado'  => 'ph-trash',
                        default      => 'ph-info'
                    };
                ?> text-xl"></i>
            </div>
            <div class="ms-3 text-sm font-semibold pr-4">
                <?php
                    echo match($_GET['msg']) {
                        'guardado'   => '¡Componentes guardados con éxito!',
                        'actualizado'=> '¡El componente fue actualizado correctamente!',
                        'devuelto'   => '¡El componente está disponible en bodega!',
                        'eliminado'  => 'Componente removido del inventario.',
                        'error_devolver' => 'No se pudo devolver el componente. Verifica que siga asignado a un equipo.',
                        default      => 'Acción procesada.'
                    };
                ?>
            </div>
            <button type="button" onclick="document.getElementById('toast-alert').remove()" class="ms-auto -mx-1.5 -my-1.5 bg-white text-slate-400 hover:text-slate-900 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <script>setTimeout(() => { document.getElementById('toast-alert')?.remove(); }, 4000);</script>
    <?php endif; ?>

   <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Gestión de Componentes</h2>
            <p class="text-slate-500 mt-1 text-sm">Control de componentes asignados a equipos o disponibles en bodega</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 flex-wrap">
            
            <div class="flex items-center gap-2 min-w-[250px]">
                <div class="w-full relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                    <input type="search" id="buscador-componentes" placeholder="Buscar por equipo, serie o componente..."
                        class="w-full p-3 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                
                <?php if (!empty($equipo_seleccionado)): ?>
                    <a href="/componentes" class="p-3 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm" title="Limpiar Filtro">
                        <i class="ph ph-x-circle text-xl flex"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="bg-slate-50 border border-slate-200/60 px-5 py-2.5 rounded-xl flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                    <i class="ph ph-cpu text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">OPERATIVOS</p>
                    <h3 id="contador-componentes" class="text-lg font-black text-slate-800 leading-none"><?= $totalComponentes ?></h3>
                </div>
            </div>

            <div class="bg-rose-50 border border-rose-200/60 px-5 py-2.5 rounded-xl flex items-center gap-3">
                <div class="w-8 h-8 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center">
                    <i class="ph ph-warning-circle text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500">DE BAJA</p>
                    <h3 id="contador-componentes-baja" class="text-lg font-black text-rose-700 leading-none"><?= $componentesBaja ?></h3>
                </div>
            </div>

            <?php if ($canCreateComponentes): ?>
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2 text-sm">
                <i class="ph ph-plus-bold text-base"></i>
                Nuevo Componente
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest font-bold">
                        <th class="p-4 pl-6">Disponibilidad / Equipo</th>
                        <th class="p-4">Tipo Hardware</th>
                        <th class="p-4">Capacidad / Detalle</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4 text-center pr-6">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(count($componentes) > 0): ?>
                        <?php foreach($componentes as $c): ?>
                            <?php
                                                $estaDisponible = empty($c['equipo_id']);
                                                $esBaja = in_array($c['estado'], ['Dañado', 'Malo'], true);
                                                $textoBusqueda = implode(' ', array_filter([
                                                    $esBaja ? 'De baja' : '',
                                                    $estaDisponible ? 'Disponible Bodega' : 'Asignado Ocupado',
                                                    $c['equipo_nombre'] ?? '',
                                                    $c['equipo_serie'] ?? '',
                                    $c['tipo'] ?? '',
                                    $c['marca_modelo'] ?? '',
                                    $c['descripcion'] ?? '',
                                    $c['estado'] ?? ''
                                ]));
                            ?>
                            <tr class="fila-componente hover:bg-slate-50/80 transition text-sm text-slate-700" data-busqueda="<?= htmlspecialchars($textoBusqueda, ENT_QUOTES, 'UTF-8') ?>" data-operativo="<?= $esBaja ? '0' : '1' ?>" data-baja="<?= $esBaja ? '1' : '0' ?>">
                                <td class="p-4 pl-6 font-bold text-slate-800">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center <?= isset($c['equipo_nombre']) ? 'bg-blue-50 text-blue-600' : 'bg-slate-50 text-slate-400' ?>">
                                            <i class="ph ph-desktop font-bold"></i>
                                        </div>
                                        <?php if ($estaDisponible): ?>
                                            <span class="text-emerald-700 font-semibold">Disponible</span>
                                            <span class="block text-xs text-slate-400 font-normal">En bodega</span>
                                        <?php else: ?>
                                            <span class="block"><?= htmlspecialchars($c['equipo_nombre'] ?? 'Equipo no encontrado') ?></span>
                                            <span class="block text-xs text-blue-600 font-semibold">Ocupado · Asignado a equipo</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-4 font-semibold text-slate-800">
                                    <div class="flex flex-col">
                                        <span><?= htmlspecialchars($c['tipo']) ?></span>
                                        <span class="text-xs text-slate-400 font-normal"><?= htmlspecialchars($c['marca_modelo'] ?? 'Genérico') ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-slate-500 max-w-xs truncate italic">"<?= htmlspecialchars($c['descripcion'] ?: 'Sin especificaciones') ?>"</td>
                                <td class="p-4">
                                    <?php 
                                        $badgeClass = match($c['estado']) {
                                            'Bueno'   => 'bg-green-50 text-green-700 border-green-200',
                                            'Regular' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                            'Dañado', 'Malo' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default   => 'bg-slate-50 text-slate-700 border-slate-200'
                                        };
                                    ?>
                                    <span class="px-3 py-1 border rounded-full text-xs font-bold uppercase tracking-wider <?= $badgeClass ?>">
                                        <?= in_array($c['estado'], ['Dañado', 'Malo'], true) ? 'De baja' : htmlspecialchars($c['estado']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center pr-6">
                                    <div class="flex justify-center gap-2">
                                        <?php if ($canEditComponentes): ?>
                                        <a href="/componentes/editar?id=<?= $c['id'] ?>" class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Editar Componente">
                                            <i class="ph ph-pencil-line text-lg"></i>
                                        </a>
                                        <?php if (!$estaDisponible): ?>
                                        <form action="/componentes/devolver" method="POST" onsubmit="return confirm('¿Devolver este componente a bodega y dejarlo disponible?')">
                                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                            <button type="submit" class="p-2.5 bg-emerald-50 text-emerald-700 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Devolver a bodega">
                                                <i class="ph ph-arrow-u-up-left text-lg"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($canDeleteComponentes): ?>
                                        <a href="/componentes/eliminar?id=<?= $c['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este componente por completo?')" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Eliminar Componente">
                                            <i class="ph ph-trash text-lg"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-3 text-slate-400">
                                    <i class="ph ph-cpu text-5xl opacity-40"></i>
                                    <p class="font-medium text-slate-500">No hay componentes registrados en este momento.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="sin-resultados-componentes" class="hidden">
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="ph ph-magnifying-glass text-3xl mb-2 text-slate-300 block"></i>
                            <span class="font-medium">No se encontraron componentes con esa búsqueda.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalComponente" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl shadow-2xl max-w-7xl w-full p-8 max-h-[90vh] flex flex-col transform scale-100 transition-all">
        
        <div class="flex justify-between items-center mb-5 flex-shrink-0">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Registrar Componentes en Lote</h3>
                <p class="text-xs text-slate-500 mt-0.5">Agrega varias piezas asignadas a equipos o disponibles en bodega.</p>
            </div>
            <button onclick="closeModal()" class="w-8 h-8 rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-all text-xl">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form action="/componentes/guardar" method="POST" class="flex-1 flex flex-col overflow-hidden">
            
            <div id="contenedor-componentes" class="space-y-4 overflow-y-auto pr-2 pb-4 flex-1">
                
                <div class="fila-componente grid grid-cols-1 md:grid-cols-12 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 relative items-end">
                    
                    <div class="md:col-span-3">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Equipo asignado (opcional)</label>
                        <select name="componentes[0][equipo_id]" class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="">-- Disponible en bodega --</option>
                            <?php foreach($equipos as $eq): ?>
                                <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nombre']) ?> (<?= htmlspecialchars($eq['serie']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tipo *</label>
                        <select name="componentes[0][tipo]" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="RAM">RAM</option>
                            <option value="Disco Duro">Disco Duro</option>
                            <option value="SSD">SSD</option>
                            <option value="Procesador">Procesador</option>
                            <option value="Tarjeta de Video">Tarjeta de Video</option>
                            <option value="Batería">Batería</option>
                            <option value="Cargador">Cargador</option>
                            <option value="Mouse">Mouse</option>
                            <option value="Teclado">Teclado</option>
                            <option value="Mouse Pad">Mouse Pad</option>
                            <option value="Ventilador">Ventilador</option>
                            <option value="Cortina de proyección">Cortina de proyección</option>
                            <option value="Adaptadores de red">Adaptadores de red</option>
                            <option value="Otro">Otro</option>
                             
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Marca / Modelo</label>
                        <input type="text" name="componentes[0][marca_modelo]" placeholder="Ej: Kingston A400" class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Capacidad / Detalle *</label>
                        <input type="text" name="componentes[0][descripcion]" placeholder="Ej: 16GB DDR4 / 480GB" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Estado *</label>
                        <select name="componentes[0][estado]" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="Dañado">Dañado</option>
                        </select>
                    </div>

                    <div class="md:col-span-1 flex justify-center pb-1">
                        <button type="button" disabled class="p-3 text-slate-300 cursor-not-allowed text-xl">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </div>

            </div>

            <div class="py-3 flex-shrink-0">
                <button type="button" onclick="agregarFilaComponente()" class="w-full py-3 border-2 border-dashed border-slate-300 text-slate-600 font-bold rounded-xl hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/20 transition flex items-center justify-center gap-2 text-sm">
                    <i class="ph ph-plus-circle-bold text-lg"></i>
                    Añadir otro componente a la lista
                </button>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100 flex-shrink-0">
                <button type="button" onclick="closeModal()" class="flex-1 py-3.5 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">Cancelar</button>
                <button type="submit" class="flex-1 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition">Guardar Todo el Lote</button>
            </div>
        </form>
    </div>
</div>

<script>
    const listaEquiposGlobal = <?= json_encode($equipos); ?>;
</script>
<script src="/js/componentes.js"></script>