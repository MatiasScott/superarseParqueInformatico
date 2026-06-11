<?php
// Mapeo seguro proveniente de tu base de datos
$acta = $acta ?? [];
$detalles = $acta['equipos'] ?? [];
$celulares = $acta['celulares'] ?? []; 

// Identificamos dinámicamente si hay alguna laptop o pc de escritorio en el listado para marcar las casillas superiores
$esLaptop = '';
$esDesktop = '';

foreach ($detalles as $eq) {
    $tipoL = strtolower($eq['tipo'] ?? '');
    if ($tipoL === 'laptop') {
        $esLaptop = 'X';
    } elseif ($tipoL === 'desktop' || $tipoL === 'computadora' || $tipoL === 'servidor') {
        $esDesktop = 'X';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Asignación - <?= htmlspecialchars($acta['codigo_acta'] ?? '') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Arial', sans-serif; }
        
        /* 🌟 CONFIGURACIÓN ESTRICTA PARA IMPRIMIR SOLO EL FORMATO DE HOJA */
        @page {
            size: auto;
            margin: 0mm; /* Elimina las cabeceras automáticas del navegador (fecha, url, título) */
        }
        
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                padding: 15mm !important; /* Margen limpio para las hojas reales */
                margin: 0; 
                background: white;
            }
            .no-print { display: none !important; } /* Oculta la barra de botones */
            .page-break { page-break-before: always; }
        }
        /* Bordes negros, delgados y sólidos para emular exactamente el formato de las imágenes */
        table, th, td { border: 1px solid #000000 !important; }
    </style>
</head>
<body class="bg-white text-black p-4 max-w-4xl mx-auto text-xs">

    <div class="no-print mb-4 p-3 bg-slate-100 rounded-xl flex justify-between items-center border border-slate-200">
        <span class="font-medium text-slate-600">Formato Oficial Integrado (Vista de Impresión Dinámica)</span>
        <div class="flex gap-2">
            <button onclick="window.close()" class="px-4 py-1.5 bg-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-300 text-xs transition-all">Cerrar</button>
            <button onclick="window.print()" class="px-4 py-1.5 bg-slate-800 text-white font-bold rounded-lg hover:bg-slate-900 text-xs shadow-sm transition-all">Imprimir Documento</button>
        </div>
    </div>

    <div class="border border-black p-2 mb-4">
        
        <div class="grid grid-cols-4 border border-black text-center items-center">
            <div class="col-span-1 p-2 font-bold text-xl tracking-tight border-r border-black">
                Superarse <br><span class="text-[9px] font-normal tracking-widest uppercase block -mt-1">Tecnológico</span>
            </div>
            <div class="col-span-2 p-2 border-r border-black font-bold uppercase text-[10px] space-y-1">
                <div>Gestión de Infraestructura y Mantenimiento</div>
                <div class="text-[10px] border-t border-black pt-1 font-black">Acta de Entrega y Recepción de Equipos de Cómputo y Accesorios Tecnológicos</div>
            </div>
            <div class="col-span-1 text-left text-[9px] p-2 space-y-0.5 font-mono">
                <div><strong>Código:</strong> ISTS-GIM-01-005</div>
                <div><strong>Versión:</strong> 001</div>
                <div><strong>Fecha:</strong> 25/04/2025</div>
                <div class="text-center border-t border-black pt-0.5 font-bold">Página 1 de 2</div>
            </div>
        </div>

        <div class="p-2 text-[11px] leading-relaxed border-x border-b border-black">
            <strong>Instrucciones:</strong> Este documento debe ser completado por el colaborador, registrando detalladamente los equipos de cómputo y accesorios tecnológicos que le han sido entregados para el cumplimiento de sus funciones institucionales.
        </div>

        <div class="mt-4">
            <h3 class="font-bold text-xs mb-1">1. Datos del Colaborador</h3>
            <table class="w-full text-left table-fixed">
                <tbody>
                    <tr>
                        <td class="p-1 font-bold w-1/4 bg-white">Nombres</td>
                        <td class="p-1 uppercase font-medium"><?= htmlspecialchars($acta['colaborador_nombre'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="p-1 font-bold bg-white">Cargo</td>
                        <td class="p-1 uppercase"><?= htmlspecialchars($acta['colaborador_cargo'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="p-1 font-bold bg-white">Correo</td>
                        <td class="p-1 text-blue-600 underline"><?= htmlspecialchars($acta['colaborador_correo'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="p-1 font-bold bg-white">Área</td>
                        <td class="p-1 uppercase"><?= htmlspecialchars($acta['colaborador_area'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="p-1 font-bold bg-white">Fecha</td>
                        <td class="p-1"><?= !empty($acta['fecha_entrega']) ? date('d/m/Y', strtotime($acta['fecha_entrega'])) : '' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <h3 class="font-bold text-xs mb-1">2. Equipos y accesorios tecnológicos</h3>
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-center font-bold">
                        <td class="p-1 w-1/3">Laptop</td>
                        <td class="p-1 w-1/12 text-center font-black text-sm"><?= $esLaptop ?></td>
                        <td class="p-1 w-1/4">Pc de Escritorio</td>
                        <td class="p-1 w-1/3 text-center font-black text-sm"><?= $esDesktop ?></td>
                    </tr>
                    <tr class="bg-gray-100 text-center font-bold uppercase tracking-wider text-[10px]">
                        <th class="p-1 text-left pl-2">Descripción del Activo</th>
                        <th class="p-1" colspan="2">Marca - Modelo / Serie</th>
                        <th class="p-1">Estado de Entrega</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($detalles)):
                        foreach($detalles as $eq): 
                            $tipoM = strtolower($eq['tipo'] ?? '');
                            if ($tipoM === 'telefono' || $tipoM === 'celular') continue;
                    ?>
                        <tr class="bg-slate-50/50">
                            <td class="p-1.5 font-bold uppercase text-slate-900">
                                🖥️ <?= htmlspecialchars($eq['nombre'] ?? $eq['tipo']) ?>
                            </td>
                            <td class="p-1.5 text-center font-mono uppercase" colspan="2">
                                <span class="font-bold block text-slate-800"><?= htmlspecialchars($eq['marca'] ?? 'S/M') ?> <?= htmlspecialchars($eq['modelo'] ?? '') ?></span>
                                <span class="text-[10px] text-gray-600 font-bold">S/N: <?= htmlspecialchars($eq['serie'] ?? 'S/S') ?></span>
                                <?php if(!empty($eq['observacion_item'])): ?>
                                    <span class="block text-[9px] italic text-gray-500 normal-case border-t border-gray-100 mt-0.5">Nota: <?= htmlspecialchars($eq['observacion_item']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-1.5 text-center uppercase font-bold text-gray-700">
                                <?= htmlspecialchars($eq['estado_entrega_equipo'] ?? 'Bueno') ?>
                            </td>
                        </tr>

                        <?php if (!empty($eq['componentes_internos']) && is_array($eq['componentes_internos'])): ?>
                            <?php foreach ($eq['componentes_internos'] as $comp): ?>
                                <tr class="text-[11px] bg-white">
                                    <td class="p-1 pl-6 text-gray-600 italic">
                                        └─ <?= htmlspecialchars($comp['tipo_componente'] ?? ($comp['nombre'] ?? 'Componente')) ?>
                                    </td>
                                    <td class="p-1 text-center font-mono text-gray-600" colspan="2">
                                        <span class="font-medium"><?= htmlspecialchars($comp['marca_modelo'] ?? ($comp['marca'] ?? 'Genérico')) ?></span>
                                       
                                        <?php if(!empty($comp['capacidad_detalle'])): ?>
                                            <span class="text-[9px] bg-slate-100 px-1 rounded text-slate-700 font-sans not-italic ml-1"><?= htmlspecialchars($comp['capacidad_detalle']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-1 text-center text-gray-500 uppercase text-[10px]">
                                        <?= htmlspecialchars($comp['estado'] ?? ($comp['estado_item'] ?? 'Bueno')) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    <?php 
                        endforeach; 
                    else:
                    ?>
                        <tr>
                            <td colspan="4" class="p-4 text-center italic text-gray-400">No hay activos de cómputo registrados en esta asignación.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="page-break border border-black p-2 mt-4">
        
        <div class="grid grid-cols-4 border border-black text-center items-center">
            <div class="col-span-1 p-2 font-bold text-xl tracking-tight border-r border-black">
                Superarse <br><span class="text-[9px] font-normal tracking-widest uppercase block -mt-1">Tecnológico</span>
            </div>
            <div class="col-span-2 p-2 border-r border-black font-bold uppercase text-[10px]">
                <div>Gestión de Infraestructura y Mantenimiento</div>
                <div class="text-[10px] border-t border-black pt-1 font-black">Acta de Entrega y Recepción de Equipos de Cómputo y Accesorios Tecnológicos</div>
            </div>
            <div class="col-span-1 text-left text-[9px] p-2 space-y-0.5 font-mono">
                <div><strong>Código:</strong> ISTS-GIM-01-005</div>
                <div><strong>Versión:</strong> 001</div>
                <div><strong>Fecha:</strong> 25/04/2025</div>
                <div class="text-center border-t border-black pt-0.5 font-bold">Página 2 de 2</div>
            </div>
        </div>

        <div class="mt-4 border border-black p-2 min-h-[50px] text-xs">
            <strong>Observación:</strong> 
            <span class="uppercase text-gray-800">
                <?= !empty($acta['observacion_general']) ? htmlspecialchars($acta['observacion_general']) : 'Los equipos que se detallan se receptan en el área de TICs' ?>
            </span>
        </div>

        <div class="mt-4">
            <h3 class="font-bold text-xs mb-1">3. Equipos telefónicos y Líneas Móviles</h3>
            <table class="w-full text-xs text-center">
                <thead>
                    <tr class="bg-gray-100 font-bold uppercase text-[10px]">
                        <th class="p-1 w-1/4 text-left">Terminal Movil</th>
                        <th class="p-1 w-1/2" colspan="3">Número / Operador / Plan</th>
                        <th class="p-1 w-1/4">Observaciones / Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $tienePlan = !empty($celulares);
                    if ($tienePlan): 
                        foreach ($celulares as $cel):
                            $marca = $cel['celular_marca'] ?? 'Celular';
                            $modelo = $cel['celular_modelo'] ?? 'Institucional';
                            $numero = $cel['numero_celular'] ?? 'S/N';
                            $operador = $cel['operador'] ?? 'S/O';
                            $plan = $cel['nombre_plan'] ?? 'Sin Plan';
                            $estado = $cel['estado_entrega_celular'] ?? ($cel['estado_entrega'] ?? 'Bueno');
                            $obs = $cel['observacion_item'] ?? ($cel['observacion_entrega'] ?? '');
                    ?>
                        <tr>
                            <td class="p-1.5 text-left font-bold uppercase">
                                <?= htmlspecialchars($marca . ' ' . $modelo) ?>
                            </td>
                            <td class="p-1.5 text-center font-mono uppercase" colspan="3">
                                <span class="font-bold text-blue-600 block text-sm"><?= htmlspecialchars($numero) ?></span>
                                <span class="text-[10px] text-gray-600"><?= htmlspecialchars($operador) ?> (<?= htmlspecialchars($plan) ?>)</span>
                            </td>
                            <td class="p-1.5 text-center uppercase text-gray-700">
                                <span class="block font-bold"><?= htmlspecialchars($estado) ?></span>
                                <?php if (!empty($obs)): ?>
                                    <span class="text-[9px] text-gray-500 normal-case italic block"><?= htmlspecialchars($obs) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <tr>
                            <td class="p-1.5 text-left font-medium text-gray-400">Marca y modelo de celular</td>
                            <td class="p-1.5 text-center font-mono text-gray-400" colspan="3">NINGUNO ASIGNADO</td>
                            <td class="p-1.5 text-center text-gray-400">N/D</td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr class="font-bold text-center">
                        <td class="p-1.5 text-left font-bold bg-gray-50">Tiene Plan Celular</td>
                        <td class="p-1 bg-white w-16 text-xs">SI</td>
                        <td class="p-1 w-12 text-sm text-center font-black"><?= $tienePlan ? 'X' : '' ?></td>
                        <td class="p-1 bg-white w-16 text-xs">NO</td>
                        <td class="p-1 w-12 text-sm text-center font-black"><?= !$tienePlan ? 'X' : '' ?></td>
                    </tr>
                    <tr>
                        <td class="p-1.5 text-left font-bold">Teléfono Fijo</td>
                        <td class="p-1.5 text-center font-mono text-gray-400" colspan="3">N/A</td>
                        <td class="p-1.5 text-center text-gray-400">N/A</td>
                    </tr>
                    <tr>
                        <td class="p-1.5 text-left font-bold">Extensión Institucional</td>
                        <td class="p-1.5 text-center font-mono text-gray-400" colspan="3">N/A</td>
                        <td class="p-1.5 text-center text-gray-400">N/A</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-justify text-xs text-gray-800 mt-6 leading-relaxed">
            Por la presente, dejo constancia de que se han receptado los siguientes accesorios tecnológicos, detallando el estado actual de cada uno al momento de la entrega. <br>
            En señal de conformidad, firman al pie del presente documento:
        </p>

        <div class="mt-6 grid grid-cols-1 border border-black text-center font-bold text-xs bg-white">
            <div class="p-2 border-b border-black bg-gray-50 uppercase tracking-wider text-[10px]">
                Elaborado Por:<br>
                <span class="text-black-600 font-black">Coordinación de TIC's</span>
            </div>
            <div class="h-24 bg-white"></div> 
            <h3 class="text-black-600">firma</h3>
            <div class="p-2 border-t border-black bg-gray-50 uppercase tracking-wider text-[10px]">
                
                <span class="text-black-600 font-black">COLABORADOR</span>
                  <br>
                <?= htmlspecialchars($acta['colaborador_nombre'] ?? 'CUSTODIO RESPONSABLE') ?>        
            </div>
            <div class="h-20 bg-white"></div> 
             <h3 class="text-black-600">firma</h3>
                
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 400);
        };
    </script>
</body>
</html>