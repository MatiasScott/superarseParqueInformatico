<?php
$documentos = $documentos ?? [];
$actas = $actas ?? [];

$canCreateDocumentos = sessionHasPermission('documentos', 'crear');
$canEditDocumentos = sessionHasPermission('documentos', 'editar');
$canDeleteDocumentos = sessionHasPermission('documentos', 'eliminar');
?>

<div class="col-span-3">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Repositorio Digital</h2>
            <p class="text-slate-500 mt-1">Gestión centralizada sobre la tabla de documentos adjuntos del sistema</p>
        </div>
        <?php if ($canCreateDocumentos): ?>
        <button onclick="openUploadModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-emerald-200 transition-all flex items-center gap-2">
            <i class="ph ph-cloud-arrow-up text-xl"></i> Subir Documento
        </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'guardado'): ?>
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-check-circle text-lg"></i> ¡Documento subido y registrado correctamente en la base de datos!
            </div>
        <?php elseif ($_GET['msg'] === 'actualizado'): ?>
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-info text-lg"></i> Los metadatos del archivo se actualizaron con éxito.
            </div>
        <?php elseif ($_GET['msg'] === 'eliminado'): ?>
            <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-trash text-lg"></i> Registro eliminado del sistema.
            </div>
        <?php elseif ($_GET['msg'] === 'extension_no_valida'): ?>
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-warning text-lg"></i> Formato denegado. El sistema restringe el almacenamiento exclusivamente a expedientes en formato .PDF
            </div>
        <?php elseif ($_GET['msg'] === 'error_archivo' || $_GET['msg'] === 'error_subida' || $_GET['msg'] === 'error_bd'): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2 text-sm font-semibold">
                <i class="ph ph-x-circle text-lg"></i> Error de transferencia. Verifique el tamaño del archivo o los parámetros del servidor.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="p-4">Documento / Archivo</th>
                        <th class="p-4">Categoría de Acta</th>
                        <th class="p-4">Vínculo Entidad</th>
                        <th class="p-4">Subido Por</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($documentos) > 0): ?>
                        <?php foreach ($documentos as $doc): ?>
                        <tr class="hover:bg-slate-50 transition text-sm text-slate-700">
                            <td class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="p-2.5 rounded-xl ph-file-pdf text-red-500 bg-red-50 text-2xl flex items-center justify-center">
                                        <i class="ph ph-file-pdf"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 block"><?= htmlspecialchars($doc['nombre_archivo']) ?></span>
                                        <span class="text-xs font-mono text-slate-400 block mt-0.5"><?= basename($doc['ruta_almacenamiento']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                    <?= htmlspecialchars($doc['tipo_documento']) ?>
                                </span>
                            </td>
                            <td class="p-4 font-medium text-slate-600">
                                <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-700">
                                    <?= $doc['tipo_entidad'] ?> (ID: <?= $doc['entidad_id'] ?>)
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="text-slate-700 font-medium"><?= htmlspecialchars($doc['usuario_creador'] ?? 'Admin') ?></div>
                                <span class="text-[11px] text-slate-400 block font-mono"><?= $doc['fecha_subida'] ?></span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1.5">
                                    <a href="/superarseParqueInformatico/public/<?= htmlspecialchars($doc['ruta_almacenamiento']) ?>" target="_blank" title="Ver / Descargar Expediente PDF" class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all">
                                        <i class="ph ph-download-simple text-lg"></i>
                                    </a>
                                    <?php if ($canEditDocumentos): ?>
                                    <button onclick="openEditModal(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['nombre_archivo'], ENT_QUOTES) ?>', '<?= htmlspecialchars($doc['tipo_documento'], ENT_QUOTES) ?>')" title="Editar Metadatos" class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all">
                                        <i class="ph ph-pencil-line text-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($canDeleteDocumentos): ?>
                                    <a href="/superarseParqueInformatico/public/documentos/eliminar?id=<?= $doc['id'] ?>" onclick="return confirm('¿Está seguro de remover permanentemente este registro lógico y borrar su archivo PDF del servidor?')" title="Eliminar Adjunto" class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all">
                                        <i class="ph ph-trash text-lg"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="p-10 text-center text-slate-400">No hay archivos adjuntos en la base de datos.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalUpload" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Adjuntar Expediente</h3>
                <p class="text-xs text-slate-400">Restringido estrictamente a documentación digitalizable</p>
            </div>
            <button onclick="closeUploadModal()" class="text-slate-400 hover:text-red-500 text-2xl"><i class="ph ph-x"></i></button>
        </div>
        
        <form action="/superarseParqueInformatico/public/documentos/guardar" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="text-sm font-bold text-slate-700">Título del Archivo</label>
                <input type="text" name="titulo" required placeholder="Ej: Acta Digitalizada - Bodega Central" class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700">Tipo Documento</label>
                    <select name="tipo_documento" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                        <option value="Acta Entrega" selected>Acta Entrega</option>
                        <option value="Informe Técnico">Informe Técnico</option>
                        <option value="Factura">Factura</option>
                        <option value="Garantía">Garantía</option>
                        <option value="Denuncia">Denuncia</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Asociar a Acta ID</label>
                    <select name="acta_id" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                        <?php foreach($actas as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['codigo_acta']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-bold text-slate-700">Archivo Adjunto (Solo PDF)</label>
                <input type="file" name="archivo" accept=".pdf" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeUploadModal()" class="flex-1 py-3 bg-slate-100 rounded-xl text-slate-600 font-bold hover:bg-slate-200 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">Subir Adjunto</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditDoc" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-800">Modificar Adjunto</h3>
            <button onclick="closeEditModal()" class="text-slate-400 text-xl hover:text-red-500"><i class="ph ph-x"></i></button>
        </div>
        
        <form action="/superarseParqueInformatico/public/documentos/actualizar" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="edit_doc_id">
            <div>
                <label class="text-sm font-bold text-slate-700">Título descriptivo</label>
                <input type="text" name="titulo" id="edit_doc_titulo" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Tipo Documento</label>
                <select name="tipo_documento" id="edit_doc_tipo" required class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500">
                    <option value="Acta Entrega">Acta Entrega</option>
                    <option value="Informe Técnico">Informe Técnico</option>
                    <option value="Factura">Factura</option>
                    <option value="Garantía">Garantía</option>
                    <option value="Denuncia">Denuncia</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-2.5 bg-slate-100 font-bold rounded-xl text-sm hover:bg-slate-200 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() { document.getElementById('modalUpload').classList.remove('hidden'); }
function closeUploadModal() { document.getElementById('modalUpload').classList.add('hidden'); }
function openEditModal(id, titulo, tipo) {
    document.getElementById('edit_doc_id').value = id;
    document.getElementById('edit_doc_titulo').value = titulo;
    document.getElementById('edit_doc_tipo').value = tipo;
    document.getElementById('modalEditDoc').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('modalEditDoc').classList.add('hidden'); }
</script>