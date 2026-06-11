<?php
// Variables e inicializaciones de seguridad
$componentes = $componentes ?? [];
$totalComponentes = count($componentes);
$equipos = $equipos ?? [];

// Variables métricas base (vienen del controlador)
$totalEquipos = $totalEquipos ?? 0;
$equiposAsignados = $equiposAsignados ?? 0;
$totalColaboradores = $totalColaboradores ?? 0;
$equiposMantenimiento = $equiposMantenimiento ?? 0;
$equiposBaja = $equiposBaja ?? 0;

// Cálculos dinámicos internos basados en la nueva estructura
$equiposDisponibles = ($totalEquipos - $equiposAsignados - $equiposMantenimiento);
$tasaUso = $totalEquipos > 0 ? round(($equiposAsignados / $totalEquipos) * 100) : 0;
$tasaSoporte = $totalEquipos > 0 ? round(($equiposMantenimiento / $totalEquipos) * 100) : 0;
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
    <div>
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Operaciones Rápidas</h3>
        <p class="text-xs text-slate-500">Accesos ágiles para la gestión del parque</p>
    </div>
    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
        <a href="/superarseParqueInformatico/public/equipos?action=nuevo" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-2xl text-xs font-bold transition shadow-sm shadow-blue-500/10">
            <i class="ph ph-plus-circle text-base"></i> Nuevo Equipo
        </a>
        <a href="/superarseParqueInformatico/public/asignaciones?action=nueva" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-2xl text-xs font-bold transition shadow-sm">
            <i class="ph ph-hand-pointing text-base"></i> Nueva Asignación
        </a>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 mb-8">

    <div class="bg-white rounded-3xl shadow-sm p-5 hover:shadow-md border border-slate-100 transition duration-300 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Total Equipos</p>
                <h3 class="text-3xl font-black text-slate-800 mt-2"><?= $totalEquipos; ?></h3>
            </div>
            <div class="bg-blue-50 text-blue-600 p-2.5 rounded-xl">
                <i class="ph ph-desktop text-xl"></i>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50/60 px-2 py-0.5 rounded-md mt-4 w-fit">
            Inventario Global
        </span>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 hover:shadow-md border border-slate-100 transition duration-300 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">En Uso</p>
                <h3 class="text-3xl font-black text-slate-800 mt-2"><?= $equiposAsignados; ?></h3>
            </div>
            <div class="bg-green-50 text-green-600 p-2.5 rounded-xl">
                <i class="ph ph-user-list text-xl"></i>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-green-600 bg-green-50/60 px-2 py-0.5 rounded-md mt-4 w-fit">
            Asignados a Personal
        </span>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 hover:shadow-md border border-slate-100 transition duration-300 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Stock Listo</p>
                <h3 class="text-3xl font-black text-blue-600 mt-2"><?= max(0, $equiposDisponibles); ?></h3>
            </div>
            <div class="bg-sky-50 text-sky-600 p-2.5 rounded-xl">
                <i class="ph ph-package text-xl"></i>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-600 bg-sky-50/60 px-2 py-0.5 rounded-md mt-4 w-fit">
            Disponibles Entrega
        </span>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 hover:shadow-md border border-slate-100 transition duration-300 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">En Taller</p>
                <h3 class="text-3xl font-black text-amber-600 mt-2"><?= $equiposMantenimiento; ?></h3>
            </div>
            <div class="bg-amber-50 text-amber-600 p-2.5 rounded-xl">
                <i class="ph ph-wrench text-xl"></i>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50/60 px-2 py-0.5 rounded-md mt-4 w-fit">
            Mantenimiento Activo
        </span>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 hover:shadow-md border border-slate-100 transition duration-300 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Componentes</p>
                <h3 class="text-3xl font-black text-slate-800 mt-2"><?= $totalComponentes > 0 ? $totalComponentes : ($componentesBuenos + $componentesRegulares + $componentesDanados); ?></h3>
            </div>
            <div class="bg-purple-50 text-purple-600 p-2.5 rounded-xl">
                <i class="ph ph-cpu text-xl"></i>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 bg-purple-50/60 px-2 py-0.5 rounded-md mt-4 w-fit">
            Piezas y Periféricos
        </span>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 hover:shadow-md border border-slate-100 transition duration-300 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 font-bold text-[11px] uppercase tracking-wider font-medium">Bajas TI</p>
                <h3 class="text-3xl font-black text-red-500 mt-2"><?= $equiposBaja ?? 0; ?></h3>
            </div>
            <div class="bg-red-50 text-red-600 p-2.5 rounded-xl">
                <i class="ph ph-trash text-xl"></i>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50/60 px-2 py-0.5 rounded-md mt-4 w-fit">
            Fuera de Servicio
        </span>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
        <div class="mb-4">
            <h4 class="text-base font-bold text-slate-800">Eficiencia y Distribución Operativa</h4>
            <p class="text-xs text-slate-400">Estado analítico porcentual de las capacidades del Parque Técnico</p>
        </div>
        
        <div class="space-y-5 my-auto">
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-bold text-slate-600 flex items-center gap-1"><i class="ph ph-chart-line text-blue-500"></i> Equipos Desplegados (Tasa de Uso)</span>
                    <span class="font-black text-slate-800"><?= $tasaUso; ?>%</span>
                </div>
                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: <?= $tasaUso; ?>%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-bold text-slate-600 flex items-center gap-1"><i class="ph ph-hourglass text-amber-500"></i> Carga de Trabajo en Soporte Técnico</span>
                    <span class="font-black text-slate-800"><?= $tasaSoporte; ?>%</span>
                </div>
                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: <?= $tasaSoporte; ?>%"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-400">
            <span class="flex items-center gap-1 font-medium"><i class="ph ph-users-three text-base"></i> Colaboradores Activos: <strong class="text-slate-700"><?= $totalColaboradores; ?></strong></span>
            <span class="text-green-500 font-bold flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span> Sincronizado</span>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
        <div>
            <h4 class="text-base font-bold text-slate-800 mb-0.5">Integridad de Componentes</h4>
            <p class="text-xs text-slate-400 mb-4">Clasificación total de piezas de recambio por estado físico</p>
        </div>

        <?php 
        $granTotalComp = ($componentesBuenos + $componentesRegulares + $componentesDanados); 
        ?>
        <div class="space-y-3.5">
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-semibold text-slate-600 flex items-center gap-1">● Operativos / Buenos</span>
                    <span class="text-slate-700 font-bold"><?= $componentesBuenos; ?> u.</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-green-500 h-full rounded-full" style="width: <?= $granTotalComp > 0 ? ($componentesBuenos / $granTotalComp) * 100 : 0; ?>%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-semibold text-slate-600 flex items-center gap-1">● Alertas / Regulares</span>
                    <span class="text-slate-700 font-bold"><?= $componentesRegulares; ?> u.</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: <?= $granTotalComp > 0 ? ($componentesRegulares / $granTotalComp) * 100 : 0; ?>%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-semibold text-slate-600 flex items-center gap-1">● Inoperativos / Dañados</span>
                    <span class="text-slate-700 font-bold"><?= $componentesDanados; ?> u.</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-red-500 h-full rounded-full" style="width: <?= $granTotalComp > 0 ? ($componentesDanados / $granTotalComp) * 100 : 0; ?>%"></div>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <a href="/superarseParqueInformatico/public/componentes" class="flex items-center justify-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 transition">
                Ir a Almacén de Componentes <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h4 class="text-base font-bold text-slate-800">Entregas de Hardware Recientes</h4>
                <p class="text-xs text-slate-400">Trazabilidad en tiempo real sobre uso de terminales</p>
            </div>
            <a href="/superarseParqueInformatico/public/asignaciones" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline">Ver todas</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-[11px] uppercase font-bold tracking-wider">
                        <th class="pb-3 font-semibold">Colaborador</th>
                        <th class="pb-3 font-semibold">Hardware de Destino</th>
                        <th class="pb-3 font-semibold">Fecha Asig.</th>
                        <th class="pb-3 font-semibold text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-600">
                    <?php if (!empty($ultimasAsignaciones) && is_array($ultimasAsignaciones)): ?>
                        <?php foreach ($ultimasAsignaciones as $asig): ?>
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="py-3.5">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($asig['colaborador_nombre']) ?></div>
                                    <div class="text-[10px] font-mono text-slate-400 bg-slate-100 w-fit px-1 rounded mt-0.5"><?= htmlspecialchars($asig['codigo_acta']) ?></div>
                                </td>
                                <td class="py-3.5">
                                    <div class="font-medium text-slate-700"><?= htmlspecialchars($asig['equipo_nombre']) ?></div>
                                    <div class="text-[10px] text-slate-400">S/N: <?= htmlspecialchars($asig['equipo_serie']) ?></div>
                                </td>
                                <td class="py-3.5 text-slate-500 font-medium"><?= date('d/m/Y', strtotime($asig['fecha_entrega'])) ?></td>
                                <td class="py-3.5 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full <?= $asig['estado_item'] === 'En Uso' ? 'text-green-700 bg-green-50 border border-green-200' : 'text-slate-600 bg-slate-100' ?>">
                                        <?= htmlspecialchars($asig['estado_item']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400 font-medium">No existen registros de entregas recientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h4 class="text-base font-bold text-slate-800">Órdenes Técnicas de Soporte</h4>
                <p class="text-xs text-slate-400">Intervenciones activas y correctivas en ejecución</p>
            </div>
            <a href="/superarseParqueInformatico/public/mantenimientos" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline">Ver bitácora</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-[11px] uppercase font-bold tracking-wider">
                        <th class="pb-3 font-semibold">Equipo Afectado</th>
                        <th class="pb-3 font-semibold">Categoría</th>
                        <th class="pb-3 font-semibold">Especialista</th>
                        <th class="pb-3 font-semibold text-right">Estatus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-600">
                    <?php if (!empty($mantenimientosCriticos) && is_array($mantenimientosCriticos)): ?>
                        <?php foreach ($mantenimientosCriticos as $mant): ?>
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="py-3.5 font-bold text-slate-800"><?= htmlspecialchars($mant['equipo_nombre']) ?></td>
                                <td class="py-3.5 text-slate-500 font-medium">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md font-semibold"><?= htmlspecialchars($mant['tipo']) ?></span>
                                </td>
                                <td class="py-3.5 text-slate-600 font-medium"><?= htmlspecialchars($mant['tecnico_responsable']) ?></td>
                                <td class="py-3.5 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full 
                                        <?= ($mant['estado'] === 'Pendiente' || $mant['estado'] === 'En Proceso') ? 'text-amber-700 bg-amber-50 border border-amber-200' : 'text-blue-700 bg-blue-50 border border-blue-200' ?>">
                                        <?= htmlspecialchars($mant['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400 font-medium">Sin procesos de mantenimiento críticos abiertos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>