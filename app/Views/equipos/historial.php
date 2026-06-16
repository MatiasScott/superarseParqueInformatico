<?php
$historial = $historial ?? [];
?>

<div class="col-span-3">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">
                Historial de Movimientos
            </h2>
            <p class="text-slate-500 mt-1">
                Auditoría y rastreo automático de cambios en el parque informático
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/equipos"
               class="bg-white border border-slate-200 text-slate-700 px-5 py-3 rounded-2xl font-bold shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="ph ph-arrow-left text-xl"></i>
                Volver a Inventario
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
        <div class="relative">
            <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
            <input
                type="text"
                id="searchInput"
                placeholder="Buscar en el historial (equipo, movimiento, observación)..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tablaHistorial">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="p-4">Fecha / Hora</th>
                        <th class="p-4">Equipo</th>
                        <th class="p-4">Marca / Serie</th>
                        <th class="p-4 text-center">Movimiento</th>
                        <th class="p-4">Responsable</th>
                        <th class="p-4">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(count($historial) > 0): ?>
                        <?php foreach($historial as $movimiento): ?>
                            <tr class="hover:bg-slate-50 transition text-sm text-slate-700">
                                <td class="p-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-800">
                                        <?= date('d/m/Y', strtotime($movimiento['fecha'])) ?>
                                    </span>
                                    <span class="block text-xs text-slate-400">
                                        <?= date('H:i', strtotime($movimiento['fecha'])) ?> hs
                                    </span>
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                                            <i class="ph ph-clock-counter-clockwise text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">
                                                <?= htmlspecialchars($movimiento['equipo_nombre']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4">
                                    <p class="font-medium text-slate-700">
                                        <?= htmlspecialchars($movimiento['equipo_marca']) ?>
                                    </p>
                                    <p class="text-xs font-mono text-slate-400">
                                        <?= htmlspecialchars($movimiento['equipo_serie']) ?>
                                    </p>
                                </td>

                                <td class="p-4 text-center">
                                    <?php 
                                        $tipo = strtolower($movimiento['tipo_movimiento']);
                                        $bgColor = 'bg-slate-50 text-slate-600';
                                        
                                        if ($tipo === 'baja') {
                                            $bgColor = 'bg-red-50 text-red-600';
                                        } elseif ($tipo === 'cambio') {
                                            $bgColor = 'bg-blue-50 text-blue-600';
                                        } elseif ($tipo === 'entrega') {
                                            $bgColor = 'bg-green-50 text-green-600';
                                        } elseif ($tipo === 'devolucion') {
                                            $bgColor = 'bg-amber-50 text-amber-600';
                                        }
                                    ?>
                                    <span class="<?= $bgColor ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                        <?= htmlspecialchars($movimiento['tipo_movimiento']) ?>
                                    </span>
                                </td>

                                <td class="p-4 font-medium text-slate-600">
                                    <?php if ($movimiento['colaborador_nombre']): ?>
                                        <div class="flex items-center gap-1.5">
                                            <i class="ph ph-user text-slate-400"></i>
                                            <?= htmlspecialchars($movimiento['colaborador_nombre']) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex items-center gap-1.5 text-slate-400 italic text-xs">
                                            <i class="ph ph-package"></i>
                                            Solo Inventario
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="p-4">
                                    <p class="text-xs text-slate-500 max-w-xs break-words line-clamp-2" title="<?= htmlspecialchars($movimiento['observacion']) ?>">
                                        <?= htmlspecialchars($movimiento['observacion']) ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-10 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="bg-slate-100 p-5 rounded-full mb-4">
                                        <i class="ph ph-history text-5xl text-slate-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700">
                                        Sin movimientos registrados
                                    </h3>
                                    <p class="text-slate-400 mt-1">
                                        Las acciones automáticas aparecerán aquí cuando crees o edites un equipo.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tablaHistorial tbody tr');
        
        rows.forEach(row => {
            if(row.cells.length > 1) { // Evita la fila de "Sin movimientos"
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(value) ? '' : 'none';
            }
        });
    });
</script>