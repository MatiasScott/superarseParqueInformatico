<?php
$colaboradores = $colaboradores ?? [];
$sedes = $sedes ?? []; // 🏢 Recibido desde el controlador para el modal de registro

// Contadores para KPIs superiores
$totalColaboradores = count($colaboradores);
$activos = count(array_filter($colaboradores, fn($c) => (int)$c['estado'] === 1));
$inactivos = $totalColaboradores - $activos;

$canCreateColaborador = sessionHasPermission('colaboradores', 'crear');
$canEditColaborador = sessionHasPermission('colaboradores', 'editar');
$canDeleteColaborador = sessionHasPermission('colaboradores', 'eliminar');
?>

<div class="col-span-3 p-1 space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                <div class="p-2.5 bg-blue-500 text-white rounded-2xl shadow-md shadow-blue-500/20">
                    <i class="ph ph-users text-2xl"></i>
                </div>
                Colaboradores
            </h2>
            <p class="text-slate-500 text-sm mt-1">Gestión de personal custodio de los recursos tecnológicos de la empresa.</p>
        </div>

        <?php if ($canCreateColaborador): ?>
            <button onclick="document.getElementById('modalColaborador').classList.remove('hidden')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-bold text-sm shadow-md shadow-blue-500/10 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <i class="ph ph-user-plus text-lg"></i>
                Nuevo Colaborador
            </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-slate-50 text-slate-600 rounded-2xl"><i class="ph ph-users-three text-2xl"></i></div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $totalColaboradores ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Personal</span>
            </div>
        </div>

        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl"><i class="ph ph-user-circle-gear text-2xl"></i></div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $activos ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Activos / Elegibles</span>
            </div>
        </div>

        <div class="bg-white border border-slate-100 p-5 rounded-3xl shadow-sm flex items-center gap-4">
            <div class="p-3 bg-slate-100 text-slate-400 rounded-2xl"><i class="ph ph-user-minus text-2xl"></i></div>
            <div>
                <span class="block text-2xl font-black text-slate-800"><?= $inactivos ?></span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Inactivos</span>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
        <div class="relative w-full sm:w-96">
            <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-lg"></i>
            <input type="text" id="buscadorColaborador" placeholder="Buscar por nombre, cargo, área o sede..."
                class="w-full bg-slate-50 pl-11 pr-4 py-3 rounded-2xl text-sm border border-slate-100 focus:outline-none focus:border-blue-500 focus:bg-white transition-all shadow-inner">
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tablaColaboradores">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="p-5">Colaborador</th>
                        <th class="p-5">Área / Cargo</th>
                        <th class="p-5">Sede / Sucursal</th>
                        <th class="p-5">Correo Electrónico</th>
                        <th class="p-5">Fecha Ingreso</th>
                        <th class="p-5">Estado</th>
                        <th class="p-5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($colaboradores)): ?>
                        <?php foreach ($colaboradores as $c): ?>
                            <tr class="fila-colaborador hover:bg-slate-50/80 transition-all text-sm">
                                <td class="p-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gradient-to-tr from-slate-100 to-slate-200 text-slate-700 rounded-xl flex items-center justify-center font-bold text-xs uppercase tracking-tighter shadow-inner">
                                            <?= substr($c['nombres'], 0, 2) ?>
                                        </div>
                                        <span class="font-bold text-slate-800 block target-buscar"><?= htmlspecialchars($c['nombres']) ?></span>
                                    </div>
                                </td>

                                <td class="p-5">
                                    <span class="font-semibold text-slate-700 block target-buscar"><?= htmlspecialchars($c['cargo'] ?? 'No asignado') ?></span>
                                    <span class="text-xs text-slate-400 target-buscar"><?= htmlspecialchars($c['area'] ?? 'Sin Área') ?></span>
                                </td>

                                <td class="p-5 font-medium text-slate-700">
                                    <span class="inline-flex items-center gap-1.5 bg-blue-50/60 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-xl text-xs font-bold target-buscar">
                                        <i class="ph ph-buildings text-sm"></i>
                                        <?= htmlspecialchars($c['sede_nombre'] ?? 'Sede Central') ?>
                                    </span>
                                </td>

                                <td class="p-5 font-medium text-slate-600">
                                    <?= htmlspecialchars($c['correo'] ?? '---') ?>
                                </td>

                                <td class="p-5 text-slate-500">
                                    <i class="ph ph-calendar text-slate-400 mr-1 inline"></i>
                                    <?= $c['fecha_ingreso'] ? date('d/m/Y', strtotime($c['fecha_ingreso'])) : 'No registrada' ?>
                                </td>

                                <td class="p-5">
                                    <?php if ((int)$c['estado'] === 1): ?>
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/60 px-2.5 py-1 rounded-xl text-xs font-bold uppercase">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 bg-slate-50 text-slate-500 border border-slate-200/60 px-2.5 py-1 rounded-xl text-xs font-bold uppercase">
                                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-5 text-center">
                                    <div class="flex justify-center gap-1.5">
                                        <?php if ($canEditColaborador): ?>
                                            <a href="/colaboradores/editar?id=<?= $c['id'] ?>"
                                                class="p-2.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                                <i class="ph ph-note-pencil text-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteColaborador): ?>
                                            <a href="/colaboradores/eliminar?id=<?= $c['id'] ?>"
                                                onclick="return confirm('¿Está seguro de eliminar este colaborador? Solo procederá si no tiene actas de asignación vinculadas.')"
                                                class="p-2.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <i class="ph ph-trash-simple text-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-16 text-center text-slate-400">
                                <i class="ph ph-users text-3xl mb-2 text-slate-300 block mx-auto"></i>
                                <h4 class="text-base font-bold text-slate-700">Sin colaboradores</h4>
                                <p class="text-xs text-slate-400 mt-1">Crea un registro para poder generar actas de entrega de equipos.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalColaborador" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 w-full max-w-xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
        <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800 tracking-tight">Registrar Nuevo Colaborador</h3>
            <button onclick="document.getElementById('modalColaborador').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl"><i class="ph ph-x"></i></button>
        </div>

        <form action="/colaboradores/guardar" method="POST" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Apellidos y Nombres *</label>
                <input type="text" name="nombres" required placeholder="Ej: Mendoza Páez Juan Carlos" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-blue-500 focus:bg-white focus:outline-none transition-all shadow-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Área Corporativa *</label>
                    <div class="relative">
                        <select
                            name="area"
                            required
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 text-sm font-medium transition cursor-pointer shadow-sm">
                            <option value="" disabled selected>Seleccione Área</option>
                            <option value="Recepción">Recepción</option>
                            <option value="Admisiones">Admisiones</option>
                            <option value="TICs">TICs</option>
                            <option value="Academica">Académico</option>
                            <option value="Finanzas">Finanzas</option>
                            <option value="Secretaria">Secretaría</option>
                            <option value="Talento Humano">Talento Humano</option>
                            <option value="Veterinaria">Veterinaria</option>
                            <option value="Promotores">Promotores</option>
                            <option value="Nexo">Nexo</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Cargo Profesional *</label>
                    <input type="text" name="cargo" required placeholder="Ej: Analista de Sistemas" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-blue-500 focus:bg-white focus:outline-none transition-all shadow-sm">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Sede / Sucursal de Trabajo *</label>
                <div class="relative">
                    <select
                        name="sede_id"
                        required
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 text-sm font-medium transition cursor-pointer shadow-sm">
                        <option value="" disabled selected>Seleccione Ubicación Física</option>
                        <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                        <i class="ph ph-caret-down text-base"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Correo Institucional *</label>
                    <input type="email" name="correo" required placeholder="ejemplo@empresa.com" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-blue-500 focus:bg-white focus:outline-none transition-all shadow-sm">
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1">Fecha de Ingreso</label>
                    <input type="date" name="fecha_ingreso" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-blue-500 focus:bg-white focus:outline-none transition-all shadow-sm">
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalColaborador').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 text-sm transition-all">Cancelar</button>
                <button type="submit" class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 text-sm shadow-md shadow-blue-500/10 transition-all">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const buscador = document.getElementById("buscadorColaborador");
        const filas = document.querySelectorAll(".fila-colaborador");

        if (buscador) {
            buscador.addEventListener("input", (e) => {
                const termino = e.target.value.toLowerCase().trim();
                filas.forEach(fila => {
                    const targets = fila.querySelectorAll(".target-buscar");
                    const textos = Array.from(targets).map(el => el.textContent.toLowerCase());
                    // Al agregar la clase target-buscar en la sede, el buscador también filtrará por sedes de forma reactiva
                    fila.style.display = textos.some(t => t.includes(termino)) ? "" : "none";
                });
            });
        }
    });
</script>