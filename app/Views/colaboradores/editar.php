<?php
if (!isset($colaborador)) {
    header("Location: /colaboradores");
    exit();
}

$sedes = $sedes ?? []; // 🏢 Recibido desde el ColaboradorController->editar()

// Definimos el arreglo de áreas para mapear el select dinámicamente
$areasDisponibles = [
    "Recepción"   => "Recepción",
    "Admisiones"  => "Admisiones",
    "TICs"        => "TICs",
    "Academica"   => "Académico",
    "Finanzas"    => "Finanzas",
    "Secretaria"  => "Secretaría"
];
?>
<div class="col-span-3 max-w-3xl mx-auto w-full py-4">
    <div class="mb-6">
        <a href="/colaboradores" class="inline-flex items-center gap-2 text-slate-400 hover:text-blue-600 transition-colors font-bold text-sm group">
            <i class="ph ph-arrow-left transition-transform group-hover:-translate-x-1"></i>
            Volver a la lista
        </a>
    </div>

    <div class="flex items-center gap-4 mb-6">
        <img
            src="https://ui-avatars.com/api/?name=<?= urlencode($colaborador['nombres']) ?>&background=2563EB&color=fff&size=128&bold=true"
            alt="Avatar de <?= htmlspecialchars($colaborador['nombres']) ?>"
            class="w-20 h-20 rounded-2xl shadow-sm border-2 border-white ring-4 ring-slate-100 object-cover"
        >

        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">
                Editar Colaborador
            </h2>
            <p class="text-slate-500 text-sm mt-0.5">
                Modificando los privilegios y datos de adscripción en el sistema.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

        <div class="bg-slate-50 border-b border-slate-100 px-8 py-5">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Registro Custodio
                    </p>
                    <h3 class="text-xl font-bold text-slate-800 mt-0.5">
                        <?= htmlspecialchars($colaborador['nombres']) ?>
                    </h3>
                </div>

                <div class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-xs font-black tracking-wider uppercase border border-blue-100/50">
                    Ficha #<?= htmlspecialchars($colaborador['id']) ?>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="/colaboradores/actualizar" method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?= htmlspecialchars($colaborador['id']) ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Apellidos y Nombres Completos *
                        </label>
                        <input
                            type="text"
                            name="nombres"
                            value="<?= htmlspecialchars($colaborador['nombres']) ?>"
                            required
                            placeholder="Ej: Mendoza Páez Juan Carlos"
                            class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-blue-500 transition-all text-sm font-medium shadow-sm"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Área Corporativa *
                        </label>
                        <div class="relative">
                            <select 
                                name="area" 
                                required 
                                class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 text-sm font-medium transition cursor-pointer shadow-sm"
                            >
                                <option value="" disabled>Seleccione Área</option>
                                <?php foreach ($areasDisponibles as $valor => $label): ?>
                                    <option value="<?= $valor ?>" <?= $colaborador['area'] === $valor ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                <i class="ph ph-caret-down text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Cargo Profesional *
                        </label>
                        <input
                            type="text"
                            name="cargo"
                            value="<?= htmlspecialchars($colaborador['cargo'] ?? '') ?>"
                            required
                            placeholder="Ej: Analista de Sistemas"
                            class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-blue-500 transition-all text-sm font-medium shadow-sm"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Correo Electrónico Institucional *
                        </label>
                        <input
                            type="email"
                            name="correo"
                            value="<?= htmlspecialchars($colaborador['correo'] ?? '') ?>"
                            required
                            placeholder="ejemplo@empresa.com"
                            class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-blue-500 transition-all text-sm font-medium shadow-sm"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Fecha de Ingreso
                        </label>
                        <input
                            type="date"
                            name="fecha_ingreso"
                            value="<?= $colaborador['fecha_ingreso'] ?? '' ?>"
                            class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-blue-500 transition-all text-sm font-medium shadow-sm"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Sede / Sucursal de Trabajo *
                        </label>
                        <div class="relative">
                            <select 
                                name="sede_id" 
                                required 
                                class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 text-sm font-bold shadow-sm cursor-pointer"
                            >
                                <option value="" disabled>Seleccione Sede</option>
                                <?php foreach($sedes as $sede): ?>
                                    <option value="<?= $sede['id'] ?>" <?= (int)$colaborador['sede_id'] === (int)$sede['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sede['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                <i class="ph ph-caret-down text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Condición del Colaborador *
                        </label>
                        <div class="relative">
                            <select 
                                name="estado" 
                                required 
                                class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 text-sm font-bold shadow-sm cursor-pointer"
                            >
                                <option value="1" <?= (int)$colaborador['estado'] === 1 ? 'selected' : '' ?>>Activo / Elegible para Asignaciones</option>
                                <option value="0" <?= (int)$colaborador['estado'] === 0 ? 'selected' : '' ?>>Inactivo / No Disponible</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                <i class="ph ph-caret-down text-base"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100">
                    <a
                        href="/colaboradores"
                        class="flex-1 py-3.5 text-center bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition-all text-sm"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-200 text-sm"
                    >
                        Guardar Cambios de Personal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>