<?php
$equipos = $equipos ?? [];
$mantenimientos = $mantenimientos ?? [];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$canEditMantenimiento = (
        !empty($_SESSION['rol']) && $_SESSION['rol'] === 'admin'
    ) || (!empty($_SESSION['permisos']['mantenimientos']['editar']));

$canDeleteMantenimiento = (
        !empty($_SESSION['rol']) && $_SESSION['rol'] === 'admin'
    ) || (!empty($_SESSION['permisos']['mantenimientos']['eliminar']));

// Contadores estadísticos dinámicos para los Kpis superiores
$totalMantenimientos = count($mantenimientos);
$pendientes = count(array_filter($mantenimientos, fn($m) => strtolower($m['estado']) === 'pendiente'));
$enProceso = count(array_filter($mantenimientos, fn($m) => in_array(strtolower($m['estado']), ['en proceso', 'en_proceso'])));
$finalizados = count(array_filter($mantenimientos, fn($m) => strtolower($m['estado']) === 'finalizado'));
?>

<div class="col-span-3 p-1 space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                <div class="p-2.5 bg-blue-500 text-white rounded-2xl shadow-md shadow-blue-500/20">
                    <i class="ph ph-wrench text-2xl"></i>
                </div>
                Gestión de Taller
            </h2>
            <p class="text-slate-500 text-sm mt-1">Historial clínico, reparaciones y soporte técnico del parque informático.</p>
        </div>
        
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-sm max-w-md">
            <i class="ph ph-info text-lg text-amber-500 shrink-0"></i>
            <span>Las órdenes de mantenimiento se originan enviando equipos directamente desde el módulo de <b>Inventario</b>.</span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="p-3 bg-slate-50 text-slate-600 rounded-2xl">
                <i class="ph ph-clipboard-text text-2xl"></i>
            </div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $totalMantenimientos ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Órdenes Totales</span>
            </div>
        </div>

        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl">
                <i class="ph ph-hourglass text-2xl"></i>
            </div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $pendientes ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Pendientes</span>
            </div>
        </div>

        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                <i class="ph ph-gear-six class-spin text-2xl animate-spin" style="animation-duration: 4s;"></i>
            </div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $enProceso ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">En Taller</span>
            </div>
        </div>

        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                <i class="ph ph-check-circle text-2xl"></i>
            </div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $finalizados ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Completados</span>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col sm:flex-row gap-3 justify-between items-center">
        <div class="relative w-full sm:w-96">
            <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-lg"></i>
            <input type="text" id="buscadorMantenimiento" placeholder="Buscar por equipo, serie o técnico..." 
                   class="w-full bg-slate-50 pl-11 pr-4 py-3 rounded-2xl text-sm border border-slate-100 focus:outline-none focus:border-blue-500 focus:bg-white transition-all shadow-inner">
        </div>
        <div class="flex gap-2 w-full sm:w-auto justify-end">
            <button onclick="window.location.reload();" class="p-3 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-2xl transition-all border border-slate-100" title="Refrescar tabla">
                <i class="ph ph-arrows-counter-clockwise text-lg"></i>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tablaMantenimientos">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="p-5">Equipo Afectado</th>
                        <th class="p-5">Tipo de Servicio</th>
                        <th class="p-5">Fecha Ingreso</th>
                        <th class="p-5">Técnico Asignado</th>
                        <th class="p-5">Estado de Orden</th>
                        <th class="p-5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($mantenimientos)): ?>
                        <?php foreach($mantenimientos as $m): 
                            $estadoActual = strtolower($m['estado']);
                            
                            // Determinar estilos visuales del badge según el estado real del ENUM
                            switch ($estadoActual) {
                                case 'finalizado':
                                    $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-200/60';
                                    $dotColor = 'bg-emerald-500';
                                    break;
                                case 'en proceso':
                                case 'en_proceso':
                                    $badgeStyle = 'bg-blue-50 text-blue-700 border-blue-200/60';
                                    $dotColor = 'bg-blue-500 animate-pulse';
                                    break;
                                case 'irreparable':
                                    $badgeStyle = 'bg-rose-50 text-rose-700 border-rose-200/60';
                                    $dotColor = 'bg-rose-500';
                                    break;
                                case 'pendiente':
                                default:
                                    $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-200/60';
                                    $dotColor = 'bg-amber-500';
                                    break;
                            }
                        ?>
                        <tr class="fila-mantenimiento hover:bg-slate-50/80 transition-all duration-200 text-sm">
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center font-bold shadow-inner">
                                        <i class="ph ph-desktop text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 block target-buscar"><?= htmlspecialchars($m['equipo_nombre']) ?></span>
                                        <span class="text-xs text-slate-400 font-mono tracking-tight target-buscar">S/N: <?= htmlspecialchars($m['equipo_serie'] ?? 'S/N') ?></span>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="p-5">
                                <span class="font-semibold text-slate-700 block"><?= htmlspecialchars($m['tipo']) ?></span>
                                <span class="text-xs text-slate-400 block max-w-xs truncate" title="<?= htmlspecialchars($m['descripcion_falla']) ?>">
                                    <?= htmlspecialchars($m['descripcion_falla']) ?>
                                </span>
                            </td>
                            
                            <td class="p-5 font-medium text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <i class="ph ph-calendar-blank text-slate-400 text-base"></i>
                                    <span><?= date('d M, Y', strtotime($m['fecha_ingreso'])) ?></span>
                                </div>
                                <span class="text-xs text-slate-400 ml-5 font-mono"><?= date('H:i a', strtotime($m['fecha_ingreso'])) ?></span>
                            </td>
                            
                            <td class="p-5">
                                <span class="font-semibold text-slate-700 block target-buscar"><?= htmlspecialchars($m['tecnico_responsable'] ?? 'Por Asignar') ?></span>
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span> Soporte Físico
                                </span>
                            </td>
                            
                            <td class="p-5">
                                <span class="inline-flex items-center gap-1.5 <?= $badgeStyle ?> border px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm">
                                    <span class="w-2 h-2 <?= $dotColor ?> rounded-full"></span>
                                    <?= htmlspecialchars(str_replace('_', ' ', $m['estado'])) ?>
                                </span>
                            </td>
                            
                            <td class="p-5 text-center">
                                <?php if ($canEditMantenimiento || $canDeleteMantenimiento): ?>
                                    <div class="flex justify-center gap-1.5">
                                        <?php if ($canEditMantenimiento): ?>
                                            <a href="/mantenimientos/editar?id=<?= $m['id'] ?>" 
                                               title="Gestionar Diagnóstico Clínico"
                                               class="p-2.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm flex items-center justify-center group">
                                                <i class="ph ph-note-pencil text-lg transition-transform group-hover:scale-110"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($canDeleteMantenimiento): ?>
                                            <a href="/mantenimientos/eliminar?id=<?= $m['id'] ?>" 
                                               onclick="return confirm('¿Está completamente seguro de eliminar esta orden del taller? El historial del equipo se verá afectado.')" 
                                               class="p-2.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all shadow-sm flex items-center justify-center group">
                                                <i class="ph ph-trash-simple text-lg transition-transform group-hover:scale-110"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Sin permisos</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-16 text-center text-slate-400">
                                <div class="w-16 h-16 bg-slate-50 text-slate-300 border border-slate-100 rounded-2xl flex items-center justify-center text-3xl mb-4 mx-auto shadow-inner">
                                    <i class="ph ph-wrench"></i>
                                </div>
                                <h4 class="text-base font-bold text-slate-700">Taller libre de órdenes</h4>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">No hay registros clínicos pendientes de revisión. Los equipos se derivan a esta sección desde el panel de Inventario.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const buscador = document.getElementById("buscadorMantenimiento");
    const filas = document.querySelectorAll(".fila-mantenimiento");

    buscador.addEventListener("input", (e) => {
        const termino = e.target.value.toLowerCase().trim();

        filas.forEach(fila => {
            const textosBusqueda = Array.from(fila.querySelectorAll(".target-buscar"))
                                        .map(el => el.textContent.toLowerCase());
            
            // Evalúa si algún elemento de la fila coincide con el input
            const coincide = textosBusqueda.some(texto => texto.includes(termino));
            
            if (coincide) {
                fila.style.display = "";
                fila.classList.remove("opacity-0");
            } else {
                fila.style.display = "none";
            }
        });
    });
});
</script>