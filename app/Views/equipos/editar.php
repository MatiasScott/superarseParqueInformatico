<?php
$equipo = $equipo ?? [];
$estados = $estados ?? [];
$sedes = $sedes ?? []; // 🏢 Recibido desde el controlador para listar las opciones
?>

<div class="max-w-5xl mx-auto">

    <div class="mb-8">
        <a
            href="/superarseParqueInformatico/public/equipos"
            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium transition mb-4">
            <i class="ph ph-arrow-left"></i>
            Volver al Inventario
        </a>

        <h2 class="text-3xl font-bold text-slate-800">
            Editar Equipo
        </h2>
        <p class="text-slate-500 mt-1">
            Modifique las especificaciones técnicas, ubicación física, costo o el estado actual del equipo ID #<?= $equipo['id'] ?>
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">

        <form
            action="/superarseParqueInformatico/public/equipos/actualizar"
            method="POST"
            class="space-y-6">

            <input
                type="hidden"
                name="id"
                value="<?= $equipo['id'] ?>">

            <div class="grid grid-cols-2 gap-5">

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Tipo de Equipo
                    </label>
                    <div class="relative">
                        <select 
                            name="tipo" 
                            required 
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-medium transition cursor-pointer">
                            
                            <option value="" disabled>Seleccione Tipo</option>
                            <option value="Laptop" <?= ($equipo['tipo'] === 'Laptop') ? 'selected' : '' ?>>Laptop / Portátil</option>
                            <option value="Desktop" <?= ($equipo['tipo'] === 'Desktop') ? 'selected' : '' ?>>Desktop / Escritorio</option>
                            <option value="Monitor" <?= ($equipo['tipo'] === 'Monitor') ? 'selected' : '' ?>>Monitor</option>
                            <option value="Teclado" <?= ($equipo['tipo'] === 'Teclado') ? 'selected' : '' ?>>Teclado</option>
                            <option value="Mouse" <?= ($equipo['tipo'] === 'Mouse') ? 'selected' : '' ?>>Mouse</option>
                            <option value="Impresora" <?= ($equipo['tipo'] === 'Impresora') ? 'selected' : '' ?>>Impresora</option>
                            <option value="Servidor" <?= ($equipo['tipo'] === 'Servidor') ? 'selected' : '' ?>>Servidor</option>
                            <option value="Otro" <?= ($equipo['tipo'] === 'Otro') ? 'selected' : '' ?>>Otro / Componente</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Nombre / Identificador
                    </label>
                    <input
                        type="text"
                        name="nombre"
                        value="<?= htmlspecialchars($equipo['nombre']) ?>"
                        required
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Marca
                    </label>
                    <input
                        type="text"
                        name="marca"
                        value="<?= htmlspecialchars($equipo['marca']) ?>"
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Modelo
                    </label>
                    <input
                        type="text"
                        name="modelo"
                        value="<?= htmlspecialchars($equipo['modelo']) ?>"
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition">
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Número de Serie
                    </label>
                    <input
                        type="text"
                        name="serie"
                        value="<?= htmlspecialchars($equipo['serie']) ?>"
                        required
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-mono transition">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Ubicación / Sede
                    </label>
                    <div class="relative">
                        <select
                            name="sede_id"
                            required
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-medium transition cursor-pointer">
                            
                            <?php foreach($sedes as $sede): ?>
                                <option
                                    value="<?= $sede['id'] ?>"
                                    <?= ((int)$sede['id'] === (int)($equipo['sede_id'] ?? 1)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sede['nombre']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Precio de Compra ($)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-slate-400 font-medium">$</span>
                        <input
                            type="number"
                            name="precio"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars($equipo['precio'] ?? '0.00') ?>"
                            required
                            class="w-full pl-7 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-mono transition">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Estado Operativo
                    </label>
                    <div class="relative">
                        <select
                            name="estado_id"
                            required
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 font-medium transition cursor-pointer">

                            <?php foreach($estados as $estado): ?>
                                <option
                                    value="<?= $estado['id'] ?>"
                                    <?= ((int)$estado['id'] === (int)$equipo['estado_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado['nombre']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-bold text-slate-700 block mb-1">
                        Descripción / Características Técnicas
                    </label>
                    <textarea
                        name="descripcion"
                        rows="4"
                        placeholder="Detalles sobre hardware, estado estético, novedades o motivos de cambio de estado..."
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 transition"><?= htmlspecialchars($equipo['descripcion'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="flex gap-3 pt-4">
                <a
                    href="/superarseParqueInformatico/public/equipos"
                    class="flex-1 py-3 bg-slate-100 text-slate-600 text-center font-bold rounded-xl hover:bg-slate-200 transition">
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Actualizar Equipo
                </button>
            </div>

        </form>
    </div>
</div>