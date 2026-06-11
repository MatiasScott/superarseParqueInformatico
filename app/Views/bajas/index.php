<div class="col-span-3">
    <div class="mb-6">
        <h2 class="text-3xl font-black text-slate-800">Equipos Dados de Baja</h2>
        <p class="text-slate-500 font-medium">Histórico de activos retirados del parque informático</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4">Equipo</th>
                        <th class="p-4">Marca / Modelo</th>
                        <th class="p-4">Fecha de Baja</th>
                        <th class="p-4">Motivo del Retiro</th>
                        <th class="p-4">Último Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    <?php if (empty($equiposBaja)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 italic">
                                No se registran equipos dados de baja en el sistema.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($equiposBaja as $eb): ?>
                        <tr class="hover:bg-rose-50/30 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-slate-800"><?= $eb['nombre'] ?></div>
                                <div class="text-xs font-mono text-slate-400">S/N: <?= $eb['serie'] ?></div>
                            </td>
                            <td class="p-4">
                                <div class="font-medium"><?= $eb['marca'] ?></div>
                                <div class="text-xs text-slate-500"><?= $eb['modelo'] ?></div>
                            </td>
                            <td class="p-4 text-rose-600 font-bold">
                                <i class="ph ph-calendar-x inline-block align-middle mr-1"></i>
                                <?= date('d/m/Y', strtotime($eb['fecha_baja'])) ?>
                            </td>
                            <td class="p-4 max-w-xs">
                                <p class="text-xs text-slate-500 italic bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    <?= !empty($eb['motivo_baja']) ? $eb['motivo_baja'] : 'No especificado' ?>
                                </p>
                            </td>
                            <td class="p-4">
                                <span class="bg-red-50 text-red-600 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase">
                                    <?= $eb['ultimo_estado'] ?? 'De Baja' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>