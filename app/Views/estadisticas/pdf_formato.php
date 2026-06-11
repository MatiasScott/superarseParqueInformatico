<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte_Auditoria_<?= date('d_m_Y') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #ffffff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        table, th, td { border: 1px solid #cbd5e1 !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 p-4 text-xs font-sans">

    <div class="no-print max-w-5xl mx-auto mb-6 p-4 bg-white rounded-xl border border-slate-200 flex justify-between items-center">
        <div>
            <span class="text-xs font-bold text-slate-700 block">Reporte Oficial Generado</span>
            <span class="text-[11px] text-slate-400">Este documento se estructuró de forma aislada para facilitar su lectura física.</span>
        </div>
        <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg text-xs hover:bg-blue-700 transition-all">
            Confirmar Impresión / Guardar PDF
        </button>
    </div>

    <div class="max-w-5xl mx-auto bg-white p-8 border border-slate-300 print:p-0 print:border-0 space-y-6">
        
        <div class="grid grid-cols-4 items-center text-center border border-slate-400 p-2">
            <div class="col-span-1 p-3 font-black text-lg border-r border-slate-400 text-left text-slate-900">SUPERARSE</div>
            <div class="col-span-2 p-2 border-r border-slate-400 font-bold uppercase text-[9px] tracking-wide text-slate-700">
                <?= ($_GET['reporte'] ?? '') === 'celulares' ? 'Auditoría Técnica de Telecomunicaciones y Líneas Móviles' : 'Reporte de Valoración de Infraestructura y Activos de Hardware' ?>
            </div>
            <div class="col-span-1 text-left p-2 text-[9px] font-mono text-slate-500 space-y-0.5">
                <div><strong>Fecha:</strong> <?= date('d/m/Y H:i') ?></div>
                <div><strong>Área:</strong> TI / Control Patrimonial</div>
            </div>
        </div>

        <?php if (($_GET['reporte'] ?? '') !== 'celulares'): ?>
            <table class="w-full text-center bg-slate-50 text-[11px] mb-4">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase text-[9px] font-bold">
                        <th class="p-2">Unidades Filtradas Totales</th>
                        <th class="p-2">Inversión del Bloque Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="font-bold text-slate-900 font-mono">
                        <td class="p-2"><?= count($listadoEquipos) ?> Equipos</td>
                        <td class="p-2 text-emerald-700">$<?= number_format(array_sum(array_column($listadoEquipos, 'precio')), 2) ?></td>
                    </tr>
                </tbody>
            </table>

            <table class="w-full text-left text-[10px]">
                <thead>
                    <tr class="bg-slate-100 font-bold text-[8px] text-slate-700 uppercase tracking-wider text-center">
                        <th class="p-2 text-left">Tipo</th>
                        <th class="p-2 text-left">Nombre del Activo</th>
                        <th class="p-2">Marca / Modelo</th>
                        <th class="p-2">Número de Serie</th>
                        <th class="p-2">Sede</th>
                        <th class="p-2 text-right">Precio Unitario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listadoEquipos)): ?>
                        <tr><td colspan="6" class="p-3 text-center text-slate-400 italic">Sin registros coincidentes.</td></tr>
                    <?php else: foreach ($listadoEquipos as $item): ?>
                        <tr>
                            <td class="p-2 uppercase font-bold text-slate-500 text-[9px]"><?= htmlspecialchars($item['tipo']) ?></td>
                            <td class="p-2 font-medium uppercase"><?= htmlspecialchars($item['nombre']) ?></td>
                            <td class="p-2 text-center uppercase text-slate-600"><?= htmlspecialchars($item['marca']) ?> / <?= htmlspecialchars($item['modelo']) ?></td>
                            <td class="p-2 text-center font-mono text-slate-600"><?= htmlspecialchars($item['serie'] ?? 'S/S') ?></td>
                            <td class="p-2 text-center uppercase text-slate-600"><?= htmlspecialchars($item['sede_nombre']) ?></td>
                            <td class="p-2 text-right font-bold font-mono text-slate-900">$<?= number_format($item['precio'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

        <?php else: ?>
            <table class="w-full text-center bg-slate-50 text-[11px] mb-4">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase text-[9px] font-bold">
                        <th class="p-2">Líneas en Reporte</th>
                        <th class="p-2">Costo Fijo Mensual</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="font-bold text-slate-900 font-mono">
                        <td class="p-2"><?= count($listadoCelulares) ?> Planes</td>
                        <td class="p-2 text-blue-700">$<?= number_format(array_sum(array_column($listadoCelulares, 'costo_mensual')), 2) ?></td>
                    </tr>
                </tbody>
            </table>

            <table class="w-full text-left text-[10px]">
                <thead>
                    <tr class="bg-slate-100 font-bold text-[8px] text-slate-700 uppercase tracking-wider text-center">
                        <th class="p-2 text-left">Número Móvil</th>
                        <th class="p-2 text-left">Operador</th>
                        <th class="p-2 text-left">Plan / Celular Asociado</th>
                        <th class="p-2">Sede Custodio</th>
                        <th class="p-2 text-center">Estado</th>
                        <th class="p-2 text-right">Gasto Mes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listadoCelulares)): ?>
                        <tr><td colspan="6" class="p-3 text-center text-slate-400 italic">Sin registros coincidentes.</td></tr>
                    <?php else: foreach ($listadoCelulares as $cel): ?>
                        <tr>
                            <td class="p-2 font-mono font-bold text-slate-900"><?= htmlspecialchars($cel['numero_celular']) ?></td>
                            <td class="p-2 font-bold uppercase text-slate-700 text-[9px]"><?= htmlspecialchars($cel['operador']) ?></td>
                            <td class="p-2">
                                <strong class="block text-slate-800"><?= htmlspecialchars($cel['nombre_plan']) ?></strong>
                                <span class="text-[9px] text-slate-400 block font-mono">Eq: <?= htmlspecialchars($cel['celular_marca'] ?? 'N/A') ?></span>
                            </td>
                            <td class="p-2 text-center uppercase text-slate-600">
                                <span class="block font-bold"><?= htmlspecialchars($cel['sede_nombre'] ?? 'BODEGA') ?></span>
                                <span class="block text-[8px] text-slate-400 lowercase">r: <?= htmlspecialchars($cel['responsable'] ?? 'sin asignar') ?></span>
                            </td>
                            <td class="p-2 text-center uppercase font-semibold text-slate-700 text-[9px]"><?= htmlspecialchars($cel['estado_plan']) ?></td>
                            <td class="p-2 text-right font-bold font-mono text-blue-700">$<?= number_format($cel['costo_mensual'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>