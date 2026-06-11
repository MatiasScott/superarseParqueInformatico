<?php
$equipos = $equipos ?? [];
$mantenimiento = $mantenimiento ?? [];
?>
<div class="max-w-4xl mx-auto py-8 px-4">
    
    <a href="/superarseParqueInformatico/public/mantenimientos" class="inline-flex items-center gap-2 text-slate-400 hover:text-blue-600 transition-colors mb-6 font-bold text-sm group">
        <i class="ph ph-arrow-left transition-transform group-hover:-translate-x-1"></i>
        Volver al taller
    </a>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        
        <div class="bg-slate-50 border-b border-slate-100 px-8 py-6">
            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Actualizar Diagnóstico Clínico</h3>
            <p class="text-slate-500 text-sm mt-0.5">Modificando orden de taller #<?= htmlspecialchars($mantenimiento['id']) ?></p>
        </div>

        <form action="/superarseParqueInformatico/public/mantenimientos/actualizar" method="POST" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <input type="hidden" name="id" value="<?= htmlspecialchars($mantenimiento['id']) ?>">

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Equipo Vinculado</label>
                <input type="hidden" name="equipo_id" value="<?= htmlspecialchars($mantenimiento['equipo_id']) ?>">
                <select disabled class="w-full p-3 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl outline-none cursor-not-allowed font-medium text-sm shadow-sm">
                    <?php foreach($equipos as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= $e['id'] == $mantenimiento['equipo_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?> (S/N: <?= htmlspecialchars($e['serie'] ?? 'S/N') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Tipo de Servicio *</label>
                <select name="tipo" required class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all">
                    <option value="Preventivo" <?= $mantenimiento['tipo'] == 'Preventivo' ? 'selected' : '' ?>>Preventivo</option>
                    <option value="Correctivo" <?= $mantenimiento['tipo'] == 'Correctivo' ? 'selected' : '' ?>>Correctivo</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Técnico Responsable *</label>
                <select name="tecnico_responsable" required class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all">
                    <option value="Matias Valdivieso" <?= $mantenimiento['tecnico_responsable'] == 'Matias Valdivieso' ? 'selected' : '' ?>>Matias Valdivieso</option>
                    <option value="Alexander Quinga" <?= $mantenimiento['tecnico_responsable'] == 'Alexander Quinga' ? 'selected' : '' ?>>Alexander Quinga</option>
                    <option value="Alisson Ortiz" <?= $mantenimiento['tecnico_responsable'] == 'Alisson Ortiz' ? 'selected' : '' ?>>Alisson Ortiz</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Fecha de Ingreso *</label>
                <input type="datetime-local" name="fecha_ingreso" value="<?= date('Y-m-d\TH:i', strtotime($mantenimiento['fecha_ingreso'])) ?>" required 
                       class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all">
            </div>

            <div>
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Fecha de Salida (Opcional)</label>
                <input type="datetime-local" name="fecha_salida" value="<?= !empty($mantenimiento['fecha_salida']) ? date('Y-m-d\TH:i', strtotime($mantenimiento['fecha_salida'])) : '' ?>" 
                       class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Estado del Trabajo *</label>
                <select name="estado" required class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all font-bold text-slate-700">
                    <option value="Pendiente" <?= $mantenimiento['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En Proceso" <?= $mantenimiento['estado'] == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Finalizado" <?= $mantenimiento['estado'] == 'Finalizado' ? 'selected' : '' ?>>Finalizado (Liberar Equipo)</option>
                    <option value="Irreparable" <?= $mantenimiento['estado'] == 'Irreparable' ? 'selected' : '' ?>>Irreparable (Dar de Baja)</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Descripción del Problema / Falla Inicial *</label>
                <textarea name="descripcion_falla" rows="3" required placeholder="Detalle los síntomas reportados del equipo..."
                          class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all"><?= htmlspecialchars($mantenimiento['descripcion_falla']) ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Tareas Realizadas (Bitácora Técnica)</label>
                <textarea name="tareas_realizadas" rows="3" placeholder="Ej: Cambio de pasta térmica, reinstalación de SO, sustitución de módulo RAM dañado..."
                          class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all"><?= htmlspecialchars($mantenimiento['tareas_realizadas'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Observaciones Adicionales / Recomendaciones</label>
                <textarea name="observaciones" rows="2" placeholder="Notas de control o recomendaciones para el usuario final..."
                          class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:outline-none transition-all"><?= htmlspecialchars($mantenimiento['observaciones'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2 flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100">
                <a href="/superarseParqueInformatico/public/mantenimientos" 
                   class="flex-1 py-3.5 text-center bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition-all text-sm">
                    Cancelar
                </a>
                <button type="submit" 
                        class="flex-1 py-3.5 bg-blue-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all text-sm">
                    Guardar Cambios Clínicos
                </button>
            </div>
        </form>
    </div>
</div>