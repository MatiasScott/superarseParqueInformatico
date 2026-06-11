<?php
$equipos = $equipos ?? [];
$totalEquipos = $totalEquipos ?? 0;
$estados = $estados ?? [];
$sedes = $sedes ?? []; // 🏢 Recibido desde el controlador

$canCreateEquipos = sessionHasPermission('equipos', 'crear');
$canEditEquipos = sessionHasPermission('equipos', 'editar');
$canDeleteEquipos = sessionHasPermission('equipos', 'eliminar');
?>

<div class="col-span-3">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicado'): ?>
        <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl shadow-sm">
            <i class="ph ph-warning-circle text-2xl text-red-500"></i>
            <div>
                <span class="font-bold">¡Entrada Duplicada!</span> El número de serie <strong class="font-mono bg-red-100 px-2 py-0.5 rounded text-sm"><?= htmlspecialchars($_GET['serie']) ?></strong> ya está registrado en el sistema con otro equipo.
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl shadow-sm">
            <i class="ph ph-check-circle text-2xl text-emerald-500"></i>
            <div>
                <span class="font-bold">¡Registro Exitoso!</span> El equipo ha sido ingresado correctamente al almacén de la sede correspondiente.
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
        <div class="mb-6 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-5 py-4 rounded-2xl shadow-sm">
            <i class="ph ph-info text-2xl text-blue-500"></i>
            <div>
                <span class="font-bold">¡Actualización Exitosa!</span> Los datos técnicos, sede y valor del equipo se modificaron correctamente.
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
        <div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-4 rounded-2xl shadow-sm">
            <i class="ph ph-trash text-2xl text-amber-500"></i>
            <div>
                <span class="font-bold">Equipo Eliminado.</span> Se removió el dispositivo y se liberaron sus dependencias.
            </div>
        </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">
                Inventario de Equipos
            </h2>
            <p class="text-slate-500 mt-1">
                Gestión completa del parque informático institucional
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-2xl shadow-sm">
                <p class="text-xs uppercase tracking-wider text-slate-400">
                    Total
                </p>
                <h3 class="text-2xl font-bold text-slate-800">
                    <?= $totalEquipos ?>
                </h3>
            </div>

            <?php if ($canCreateEquipos): ?>
            <button
                onclick="openModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                <i class="ph ph-desktop text-xl"></i>
                Nuevo Equipo
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
        <div class="relative">
            <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
            <input
                type="text"
                id="searchInput"
                placeholder="Buscar equipo por nombre, marca, modelo, serie, tipo o sede..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tablaEquipos">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="p-4">Equipo</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Marca/Modelo</th>
                        <th class="p-4">Serie</th>
                        <th class="p-4">Ubicación / Sede</th>
                        <th class="p-4 text-right">Precio</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(count($equipos) > 0): ?>
                        <?php foreach($equipos as $e): ?>
                            <tr class="hover:bg-slate-50 transition text-sm text-slate-700">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                                            <i class="ph ph-desktop text-2xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">
                                                <?= htmlspecialchars($e['nombre']) ?>
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                ID #<?= $e['id'] ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-medium">
                                    <?= htmlspecialchars($e['tipo']) ?>
                                </td>
                                <td class="p-4">
                                    <span class="font-semibold block text-slate-700"><?= htmlspecialchars($e['marca']) ?></span>
                                    <span class="text-xs text-slate-400"><?= htmlspecialchars($e['modelo']) ?></span>
                                </td>
                                <td class="p-4 font-mono text-slate-500">
                                    <?= htmlspecialchars($e['serie']) ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 text-slate-700">
                                        <i class="ph ph-map-pin text-slate-400 text-base"></i>
                                        <span class="font-medium"><?= htmlspecialchars($e['sede_nombre'] ?? 'Sin asignar') ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-right font-mono font-bold text-slate-800">
                                    $<?= number_format($e['precio'] ?? 0, 2) ?>
                                </td>
                                <td class="p-4 text-center">
                                    <?php
                                        $nombreEstado = strtolower($e['estado_nombre'] ?? '');
                                        $badgeClass = "bg-slate-50 text-slate-600"; 

                                        if($nombreEstado === 'activo') {
                                            $badgeClass = "bg-green-50 text-green-600 border border-green-100";
                                        } elseif($nombreEstado === 'mantenimiento') {
                                            $badgeClass = "bg-amber-50 text-amber-600 border border-amber-100";
                                        } elseif($nombreEstado === 'baja') {
                                            $badgeClass = "bg-red-50 text-red-600 border border-red-100";
                                        } elseif($nombreEstado === 'disponible') {
                                            $badgeClass = "bg-blue-50 text-blue-600 border border-blue-100";
                                        }
                                    ?>
                                    <span class="<?= $badgeClass ?> px-3 py-1 rounded-full text-xs font-bold capitalize inline-block">
                                        <?= htmlspecialchars($e['estado_nombre'] ?? 'Sin Estado') ?>
                                    </span>
                                </td>

                                <td class="p-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <?php if ($canEditEquipos): ?>
                                        <a
                                            href="/superarseParqueInformatico/public/equipos/editar?id=<?= $e['id'] ?>"
                                            class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                            title="Editar / Cambiar Estado u Ubicación">
                                            <i class="ph ph-pencil-line text-lg"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if ($canDeleteEquipos): ?>
                                        <a
                                            href="/superarseParqueInformatico/public/equipos/eliminar?id=<?= $e['id'] ?>"
                                            onclick="return confirm('¿Está seguro de que desea eliminar este equipo? Se borrarán de forma permanente sus historiales y órdenes de mantenimiento vinculadas.')"
                                            class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm"
                                            title="Eliminar">
                                            <i class="ph ph-trash text-lg"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-10 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="bg-slate-100 p-5 rounded-full mb-4">
                                        <i class="ph ph-desktop text-5xl text-slate-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700">
                                        No hay equipos registrados
                                    </h3>
                                    <p class="text-slate-400 mt-1">
                                        Registra el primer equipo del inventario institucional
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

<div
    id="modalEquipo"
    class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">
                    Registrar Equipo
                </h3>
                <p class="text-slate-500 text-sm mt-1">
                    Complete la información inicial técnica del equipo. Todo equipo nuevo ingresa como <b>Disponible</b> en bodega.
                </p>
            </div>

            <button
                onclick="closeModal()"
                class="text-slate-400 hover:text-red-500 text-2xl transition">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form
            action="/superarseParqueInformatico/public/equipos/guardar"
            method="POST"
            class="space-y-5">
            
            <input type="hidden" name="estado_id" value="1">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">Tipo de Equipo</label>
                    <div class="relative">
                        <select 
                            name="tipo" 
                            required 
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-medium transition cursor-pointer">
                            <option value="" disabled selected>Seleccione Tipo</option>
                            <option value="Laptop">Laptop / Portátil</option>
                            <option value="Desktop">Desktop / Escritorio</option>
                            <option value="Impresora">Impresora</option>
                            <option value="Scanner">Scanner</option>
                            <option value="Infocus">Infocus</option>
                            <option value="Componente">Componente Interno / Otro</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">Nombre / Identificador</label>
                    <input
                        type="text"
                        name="nombre"
                        placeholder="Ej. Laptop HP Oficinas 01"
                        required
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">Marca</label>
                    <input
                        type="text"
                        name="marca"
                        placeholder="Ej. HP, Dell, Lenovo"
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">Modelo</label>
                    <input
                        type="text"
                        name="modelo"
                        placeholder="Ej. ProBook 450 G8"
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition">
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-bold text-slate-700 block mb-1">Número de Serie</label>
                    <input
                        type="text"
                        name="serie"
                        required
                        placeholder="Código de serie único del fabricante"
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-mono transition">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">Sede de Ingreso</label>
                    <div class="relative">
                        <select 
                            name="sede_id" 
                            required 
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-medium transition cursor-pointer">
                            <?php foreach ($sedes as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (int)$s['id'] === 1 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">Precio de Compra ($)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-slate-400 font-medium">$</span>
                        <input
                            type="number"
                            name="precio"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            required
                            class="w-full pl-7 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-mono transition">
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-bold text-slate-700 block mb-1">Descripción / Especificaciones Técnicas</label>
                    <textarea
                        name="descripcion"
                        rows="3"
                        placeholder="Detalle de componentes (Procesador, RAM, Almacenamiento, etc.) o estado inicial de entrega..."
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition"></textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button
                    type="button"
                    onclick="closeModal()"
                    class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Guardar e Ingresar a Almacén
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modalEquipo').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('modalEquipo').classList.add('hidden');
}
</script>

<script src="/superarseParqueInformatico/public/js/equipos.js"></script>