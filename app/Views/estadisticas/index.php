<?php $sedesDisponible = isset($sedesDisponible) && is_array($sedesDisponible) ? $sedesDisponible : []; ?>

<div class="p-6 max-w-7xl mx-auto space-y-6">
    
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Estadísticas e Inteligencia de Parque</h1>
        <p class="text-sm text-slate-500">Monitoreo financiero e inventario integrado en tiempo real.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Equipos Disponibles</span>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-slate-800"><?= number_format($resumenEquipos['total_equipos'] ?? 0) ?></h3>
                <p class="text-xs text-slate-500 mt-1">Unidades en inventario de bodega</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Inversión Hardware</span>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-emerald-600">$<?= number_format($resumenEquipos['inversion_total'] ?? 0, 2) ?></h3>
                <p class="text-xs text-slate-500 mt-1">Valor capitalizado total</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Líneas Celulares</span>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-slate-800"><?= number_format($resumenCelulares['total_lineas'] ?? 0) ?></h3>
                <p class="text-xs text-slate-500 mt-1">Planes activos en total</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gasto Mensual Telecom</span>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-blue-600">$<?= number_format($resumenCelulares['gasto_mensual_total'] ?? 0, 2) ?></h3>
                <p class="text-xs text-slate-500 mt-1">Costo recurrente acumulado</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg text-lg">🧩</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Componentes en Hardware</p>
                <h4 class="text-base font-black text-slate-700"><?= number_format($resumenComponentes['total_componentes'] ?? 0) ?> Unds.</h4>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-50 text-purple-600 rounded-lg text-lg">💾</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Módulos RAM / Almacenamiento</p>
                <h4 class="text-base font-black text-slate-700"><?= number_format($resumenComponentes['total_ram'] ?? 0) ?> RAM | <?= number_format($resumenComponentes['total_almacenamiento'] ?? 0) ?> Discos</h4>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg text-lg">🛡️</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Estado de Componentes</p>
                <h4 class="text-base font-black text-emerald-600"><?= number_format($resumenComponentes['componentes_buenos'] ?? 0) ?> Operativos</h4>
            </div>
        </div>
    </div>

    <div class="border-b border-slate-200">
        <nav class="flex space-x-4" aria-label="Tabs">
            <button onclick="switchTab('tab-hardware')" id="btn-tab-hardware" class="tab-btn px-4 py-2.5 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                🖥️ Inventario de Hardware
            </button>
            <button onclick="switchTab('tab-celulares')" id="btn-tab-celulares" class="tab-btn px-4 py-2.5 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                📱 Control de Líneas y Planes
            </button>
        </nav>
    </div>

    <div id="tab-hardware" class="tab-content space-y-4 hidden">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-end gap-4 justify-between">
            <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                <input type="hidden" name="active_tab" value="hardware">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tipo de Hardware</label>
                    <select name="tipo" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="Laptop" <?= ($_GET['tipo'] ?? '') === 'Laptop' ? 'selected' : '' ?>>Laptop</option>
                        <option value="Desktop" <?= ($_GET['tipo'] ?? '') === 'Desktop' ? 'selected' : '' ?>>Desktop</option>
                        <option value="Monitor" <?= ($_GET['tipo'] ?? '') === 'Monitor' ? 'selected' : '' ?>>Monitor</option>
                        <option value="Servidor" <?= ($_GET['tipo'] ?? '') === 'Servidor' ? 'selected' : '' ?>>Servidor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sede / Ubicación</label>
                    <select name="sede_id" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Todas las Sedes --</option>
                        <?php foreach ($sedesDisponible as $sd): ?>
                            <option value="<?= $sd['id'] ?>" <?= (int)($_GET['sede_id'] ?? 0) === (int)$sd['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sd['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Buscar por Modelo</label>
                    <div class="flex gap-2">
                        <input type="text" name="modelo" value="<?= htmlspecialchars($_GET['modelo'] ?? '') ?>" placeholder="Ej: ThinkPad..." class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-colors">Filtrar</button>
                    </div>
                </div>
            </form>
            <a href="/estadisticas/imprimir?reporte=hardware&tipo=<?= urlencode($_GET['tipo']??'') ?>&sede_id=<?= urlencode($_GET['sede_id']??'') ?>&modelo=<?= urlencode($_GET['modelo']??'') ?>" 
               target="_blank" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg flex items-center gap-2 self-start md:self-end whitespace-nowrap">
                🖨️ PDF Hardware
            </a>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold">
                    <tr class="border-b border-slate-200">
                        <th class="p-3 w-10"></th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Marca/Modelo</th>
                        <th class="p-3">Serie</th>
                        <th class="p-3">Sede</th>
                        <th class="p-3 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if(empty($listadoEquipos)): ?>
                        <tr><td colspan="7" class="p-4 text-center text-slate-400 italic">No hay equipos.</td></tr>
                    <?php else: foreach($listadoEquipos as $eq): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center">
                                <?php if(!empty($eq['componentes'])): ?>
                                    <button onclick="toggleComponentes(<?= $eq['id'] ?>)" class="text-slate-400 hover:text-slate-600 font-bold focus:outline-none transition-transform" id="icon-<?= $eq['id'] ?>">▶</button>
                                <?php else: ?>
                                    <span class="text-slate-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 font-bold text-slate-600"><?= $eq['tipo'] ?></td>
                            <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($eq['nombre']) ?></td>
                            <td class="p-3 text-slate-500"><?= htmlspecialchars($eq['marca']) ?> / <?= htmlspecialchars($eq['modelo']) ?></td>
                            <td class="p-3 font-mono text-slate-600"><?= htmlspecialchars($eq['serie'] ?? 'S/S') ?></td>
                            <td class="p-3 uppercase text-slate-600"><?= htmlspecialchars($eq['sede_nombre']) ?></td>
                            <td class="p-3 text-right font-bold font-mono">$<?= number_format($eq['precio'], 2) ?></td>
                        </tr>
                        <?php if(!empty($eq['componentes'])): ?>
                            <tr id="comp-row-<?= $eq['id'] ?>" class="hidden bg-slate-50/50">
                                <td></td>
                                <td colspan="6" class="p-3">
                                    <div class="border border-slate-200 rounded-lg bg-white p-3 space-y-2">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">🛠️ Componentes Internos Installed</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                            <?php foreach($eq['componentes'] as $comp): ?>
                                                <div class="p-2 rounded border border-slate-100 bg-slate-50 flex flex-col justify-between">
                                                    <div>
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700 uppercase"><?= htmlspecialchars($comp['tipo_componente']) ?></span>
                                                        <p class="text-xs font-bold text-slate-700 mt-1"><?= htmlspecialchars($comp['marca_modelo']) ?></p>
                                                        <p class="text-[11px] text-slate-500"><?= htmlspecialchars($comp['capacidad_detail'] ?? $comp['capacidad_detalle'] ?? 'N/D') ?></p>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-200/60 text-[10px]">
                                                        <span class="font-mono text-slate-400">S/N: <?= htmlspecialchars($comp['serie'] ?: 'S/S') ?></span>
                                                        <span class="font-bold <?= $comp['estado'] === 'Bueno' ? 'text-emerald-600' : 'text-amber-600' ?>"><?= htmlspecialchars($comp['estado']) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-celulares" class="tab-content space-y-4 hidden">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-end gap-4 justify-between">
            <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                <input type="hidden" name="active_tab" value="celulares">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Operador Telefónico</label>
                    <select name="operador" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Todos los Operadores --</option>
                        <option value="Claro" <?= ($_GET['operador'] ?? '') === 'Claro' ? 'selected' : '' ?>>Claro</option>
                        <option value="Movistar" <?= ($_GET['operador'] ?? '') === 'Movistar' ? 'selected' : '' ?>>Movistar</option>
                        <option value="CNT" <?= ($_GET['operador'] ?? '') === 'CNT' ? 'selected' : '' ?>>CNT</option>
                        <option value="Tuenti" <?= ($_GET['operador'] ?? '') === 'Tuenti' ? 'selected' : '' ?>>Tuenti</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sede / Ubicación</label>
                    <div class="flex gap-2">
                        <select name="sede_id_cel" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Todas las Sedes --</option>
                            <?php foreach ($sedesDisponible as $sd): ?>
                                <option value="<?= $sd['id'] ?>" <?= (int)($_GET['sede_id_cel'] ?? 0) === (int)$sd['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sd['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-colors">Filtrar</button>
                    </div>
                </div>
            </form>
            <a href="/estadisticas/imprimir?reporte=celulares&operador=<?= urlencode($_GET['operador']??'') ?>&sede_id_cel=<?= urlencode($_GET['sede_id_cel']??'') ?>" 
               target="_blank" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg flex items-center gap-2 self-start md:self-end whitespace-nowrap">
                🖨️ Imprimir Líneas
            </a>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold">
                    <tr class="border-b border-slate-200">
                        <th class="p-3">Línea</th>
                        <th class="p-3">Operador</th>
                        <th class="p-3">Plan / Equipo</th>
                        <th class="p-3">Asignación</th>
                        <th class="p-3 text-center">Estado</th>
                        <th class="p-3 text-right">Costo Mensual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if(empty($listadoCelulares)): ?>
                        <tr><td colspan="6" class="p-4 text-center text-slate-400 italic">No hay líneas.</td></tr>
                    <?php else: foreach($listadoCelulares as $cel): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-mono font-bold text-slate-900"><?= htmlspecialchars($cel['numero_celular']) ?></td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100"><?= htmlspecialchars($cel['operador']) ?></span></td>
                            <td class="p-3"><span class="block font-bold"><?= htmlspecialchars($cel['nombre_plan']) ?></span><span class="text-[10px] text-slate-400">📱 <?= htmlspecialchars($cel['celular_marca'] ?? 'N/D') ?></span></td>
                            <td class="p-3"><span class="font-bold block"><?= htmlspecialchars($cel['sede_nombre'] ?? 'BODEGA') ?></span><span class="text-[10px] text-slate-400"><?= htmlspecialchars($cel['responsable'] ?? '') ?></span></td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700"><?= htmlspecialchars($cel['estado_plan']) ?></span></td>
                            <td class="p-3 text-right font-bold text-blue-600 font-mono">$<?= number_format($cel['costo_mensual'] ?? 0, 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-700 text-sm mb-2">Hardware por Tipo</h4>
            <div class="relative h-64">
                <canvas id="canvasHardware"></canvas>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-700 text-sm mb-2">Modelos: Inversión y Cantidad</h4>
            <div class="relative h-64">
                <canvas id="canvasModelos"></canvas>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-700 text-sm mb-2">Volumen de Componentes</h4>
            <div class="relative h-64">
                <canvas id="canvasComponentes"></canvas>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-700 text-sm mb-2">Gasto por Operador</h4>
            <div class="relative h-64 flex justify-center">
                <canvas id="canvasCelulares"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
    <?php
        // 1. Procesamiento de Hardware por tipo
        $hwLabels = []; $hwValues = [];
        if (!empty($graficoHardware)) {
            foreach ($graficoHardware as $row) {
                $rowClean = array_change_key_case($row, CASE_LOWER);
                $hwLabels[] = $rowClean['tipo'] ?? 'Desconocido';
                $hwValues[] = (float)($rowClean['inversion_subtotal'] ?? 0);
            }
        }

        // 2. Procesamiento de Modelos (Top 6 más concurrentes)
        $modelosData = [];
        if (!empty($listadoEquipos)) {
            foreach ($listadoEquipos as $eq) {
                $modNombre = !empty($eq['modelo']) ? trim($eq['modelo']) : 'Otros/Sin Modelo';
                if (!isset($modelosData[$modNombre])) {
                    $modelosData[$modNombre] = ['cantidad' => 0, 'precio_total' => 0];
                }
                $modelosData[$modNombre]['cantidad']++; 
                $modelosData[$modNombre]['precio_total'] += (float)($eq['precio'] ?? 0);
            }
        }
        uasort($modelosData, function($a, $b) { return $b['cantidad'] <=> $a['cantidad']; });
        $modelosData = array_slice($modelosData, 0, 6, true);

        $modLabels = array_keys($modelosData);
        $modCantidades = array_column($modelosData, 'cantidad');
        $modPrecios = array_column($modelosData, 'precio_total');

        // 3. Procesamiento de Componentes por Tipo
        $compLabels = []; $compValues = [];
        if (!empty($graficoComponentes)) {
            foreach ($graficoComponentes as $row) {
                $rowClean = array_change_key_case($row, CASE_LOWER);
                $compLabels[] = $rowClean['tipo_componente'] ?? 'Otros';
                $compValues[] = (int)($rowClean['total_cantidad'] ?? $rowClean['cantidad'] ?? 0);
            }
        }

        // 4. Procesamiento de Telefonía por Operador
        $celLabels = []; $celValues = [];
        if (!empty($graficoCelulares)) {
            foreach ($graficoCelulares as $row) {
                $rowClean = array_change_key_case($row, CASE_LOWER);
                $celLabels[] = $rowClean['operador'] ?? 'Otros';
                $celValues[] = (float)($rowClean['gasto_subtotal'] ?? 0);
            }
        }
    ?>

    // Inyección limpia y estructurada de datos (con las etiquetas PHP corregidas)
    window.DATA_ESTADISTICAS = {
        activeTab: '<?= $_GET['active_tab'] ?? "hardware" ?>',
        hardware: {
            labels: <?= json_encode($hwLabels) ?>,
            values: <?= json_encode($hwValues) ?>
        },
        modelos: {
            labels: <?= json_encode($modLabels) ?>,
            cantidades: <?= json_encode($modCantidades) ?>,
            precios: <?= json_encode($modPrecios) ?>
        },
        componentes: {
            labels: <?= json_encode($compLabels) ?>,
            values: <?= json_encode($compValues) ?>
        },
        celulares: {
            labels: <?= json_encode($celLabels) ?>,
            values: <?= json_encode($celValues) ?>
        }
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/js/estadistica.js"></script>