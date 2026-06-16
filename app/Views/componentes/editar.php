<?php
// Validación de seguridad para asegurar que los datos del componente existan
if (!isset($componente)) {
    header("Location: /componentes");
    exit();
}
$equipos = $equipos ?? [];
?>

<div class="col-span-3 max-w-3xl mx-auto w-full animate-fade-in">

    <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-2xl shadow-md border-4 border-white bg-blue-600 flex items-center justify-center text-white text-3xl">
            <i class="ph ph-cpu"></i>
        </div>
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Editar Componente</h2>
            <p class="text-slate-500 mt-1">Modifica las especificaciones o reasigna esta pieza a otro equipo informático</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

        <div class="bg-slate-50 border-b border-slate-100 px-8 py-5">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Pieza / Periférico</p>
                    <h3 class="text-xl font-bold text-slate-800 mt-1">
                        <?= htmlspecialchars($componente['tipo']) ?>
                    </h3>
                </div>
                <div class="bg-blue-50 text-blue-600 border border-blue-100 px-4 py-2 rounded-xl text-sm font-black tracking-wide">
                    ID #<?= $componente['id'] ?>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="/componentes/actualizar" method="POST" class="space-y-6">

                <input type="hidden" name="id" value="<?= $componente['id'] ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Asignar a Equipo (Computador)
                        </label>
                        <div class="relative">
                            <select 
                                name="equipo_id" 
                                class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition appearance-none text-slate-700 font-medium"
                            >
                                <option value="">-- Dejar Libre / En Bodega (Stock) --</option>
                                <?php foreach($equipos as $e): ?>
                                    <option value="<?= $e['id'] ?>" <?= ($e['id'] == $componente['equipo_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($e['nombre']) ?> - <?= htmlspecialchars($e['marca']) ?> (Serie: <?= htmlspecialchars($e['serie']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <i class="ph ph-caret-down text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Tipo de Componente
                        </label>
                        <input
                            type="text"
                            name="tipo"
                            value="<?= htmlspecialchars($componente['tipo']) ?>"
                            placeholder="Ej: Memoria RAM, Disco SSD"
                            required
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition text-slate-700 font-medium shadow-sm"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Estado Actual
                        </label>
                        <div class="relative">
                            <select 
                                name="estado" 
                                required
                                class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition appearance-none text-slate-700 font-medium"
                            >
                                <option value="Bueno" <?= ($componente['estado'] == 'Bueno') ? 'selected' : '' ?>>Bueno</option>
                                <option value="Regular" <?= ($componente['estado'] == 'Regular') ? 'selected' : '' ?>>Regular</option>
                                <option value="Dañado" <?= ($componente['estado'] == 'Dañado') ? 'selected' : '' ?>>Dañado</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <i class="ph ph-caret-down text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Descripción / Detalles Técnicos
                        </label>
                        <textarea
                            name="descripcion"
                            rows="3"
                            required
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition text-slate-700 font-medium shadow-sm"
                            placeholder="Indique marca, modelo exacto, capacidad o números seriales de la pieza..."
                        ><?= htmlspecialchars($componente['descripcion']) ?></textarea>
                    </div>

                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-slate-100">
                    <a
                        href="/componentes"
                        class="flex-1 py-4 text-center bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition-all"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="flex-1 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-100 hover:shadow-blue-200"
                    >
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>