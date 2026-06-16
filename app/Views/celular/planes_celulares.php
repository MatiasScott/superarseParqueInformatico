<?php // Vista: listado, detalles, alertas e inserciones dinámicas ?>
<?php
$canCreateTelefonia = sessionHasPermission('celular', 'crear');
$canEditTelefonia = sessionHasPermission('celular', 'editar');
$canDeleteTelefonia = sessionHasPermission('celular', 'eliminar');
?>
<div x-data="{ openCrear: false, openEditar: false, selectItem: null, editItem: {} }" class="space-y-6 col-span-3">

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="p-4 mb-4 text-sm text-emerald-800 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center gap-2 shadow-sm">
            <i class="ph-bold ph-check-circle text-lg text-emerald-500"></i>
            <span><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="p-4 mb-4 text-sm text-rose-800 bg-rose-50 rounded-2xl border border-rose-200 flex items-center gap-2 shadow-sm">
            <i class="ph-bold ph-warning-circle text-lg text-rose-500"></i>
            <span><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h3 class="text-3xl font-bold text-slate-800 tracking-tight">Inventario Integrado de Telefonía</h3>
            <p class="text-sm text-slate-500 mt-1">Control de líneas institucionales, planes de datos y especificaciones de terminales móviles.</p>
        </div>
        
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <div class="bg-slate-50 border border-slate-200 px-5 py-2.5 rounded-xl shadow-sm min-w-[100px] text-center">
                <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Total Líneas</p>
                <h3 class="text-xl font-black text-slate-800"><?= !empty($items) ? count($items) : 0 ?></h3>
            </div>
            <?php if ($canCreateTelefonia): ?>
            <button @click="openCrear = true" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-3 rounded-xl text-xs shadow-md shadow-blue-500/10 transition-all whitespace-nowrap">
                <i class="ph-bold ph-plus text-sm"></i>
                Registrar Línea & Celular
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <div id="searchContainer" class="flex items-center gap-2 w-full">
            <div class="relative w-full">
                <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-base"></i>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Escriba aquí para buscar por celular, operador, plan, marca, modelo o IMEI..."
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 font-medium">
            </div>
            
            <button 
                type="button"
                id="clearSearchBtn" 
                class="hidden p-3 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm flex items-center justify-center h-[42px] w-[42px]" 
                title="Limpiar filtro de búsqueda">
                <i class="ph ph-x-circle text-lg"></i>
            </button>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="tablaTelefonia">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="p-4">Línea Móvil</th>
                        <th class="p-4">Plan / Costo</th>
                        <th class="p-4">Aparato Celular</th>
                        <th class="p-4">Identificadores Técnicos</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (!empty($items)): foreach ($items as $item): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors text-slate-700">
                            <td class="p-4">
                                <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                    <i class="ph ph-phone text-blue-500"></i>
                                    <?= htmlspecialchars($item['numero_celular']) ?>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded-md font-bold mt-1 inline-block uppercase tracking-wide <?= strtolower($item['operador']) == 'claro' ? 'bg-red-50 text-red-600' : (strtolower($item['operador']) == 'movistar' ? 'bg-sky-50 text-sky-600' : 'bg-gray-100 text-gray-700') ?>">
                                    <?= htmlspecialchars($item['operador']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="text-slate-900 font-bold"><?= htmlspecialchars($item['nombre_plan']) ?></div>
                                <div class="text-slate-400 text-[11px] mt-0.5">$<?= number_format($item['costo_mensual'], 2) ?> / mes</div>
                                <div class="text-[10px] text-slate-500 italic mt-0.5"><?= htmlspecialchars($item['tipo_sim']) ?></div>
                            </td>
                            <td class="p-4">
                                <div class="text-slate-900 font-bold uppercase"><?= htmlspecialchars($item['celular_marca'] . ' ' . $item['celular_modelo']) ?></div>
                                <div class="flex gap-2 text-[10px] text-slate-400 mt-1">
                                    <span>Capacidad: <b class="text-slate-600"><?= htmlspecialchars($item['celular_almacenamiento']) ?></b></span>
                                    <span>Color: <b class="text-slate-600"><?= htmlspecialchars($item['celular_color']) ?></b></span>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-[11px] space-y-0.5 text-slate-600">
                                <div><span class="text-slate-400 font-sans text-[10px]">IMEI:</span> <?= htmlspecialchars($item['celular_imei_1']) ?></div>
                                <div><span class="text-slate-400 font-sans text-[10px]">Serie:</span> <?= htmlspecialchars($item['celular_serie']) ?></div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide
                                    <?= $item['estado_plan'] === 'Disponible' ? 'bg-emerald-50 text-emerald-700' : 
                                        ($item['estado_plan'] === 'Asignado' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') ?>">
                                    <?= htmlspecialchars($item['estado_plan']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button 
                                        type="button"
                                        @click="selectItem = <?= htmlspecialchars(json_encode($item)) ?>" 
                                        class="p-2 bg-sky-50 text-sky-600 rounded-xl hover:bg-sky-600 hover:text-white transition-all shadow-sm" 
                                        title="Ver Ficha Técnica">
                                        <i class="ph ph-eye text-lg"></i>
                                    </button>

                                    <?php if ($canEditTelefonia): ?>
                                    <button 
                                        type="button"
                                        @click="editItem = <?= htmlspecialchars(json_encode($item)) ?>; openEditar = true" 
                                        class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" 
                                        title="Editar / Cambiar Estado">
                                        <i class="ph ph-pencil-line text-lg"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($canDeleteTelefonia): ?>
                                    <a 
                                        href="/planes-celulares/eliminar?id=<?= $item['id'] ?>" 
                                        onclick="return confirm('¿Está seguro de que desea eliminar este equipo? Se borrarán de forma permanente sus historiales y órdenes de mantenimiento vinculadas.')" 
                                        class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" 
                                        title="Eliminar">
                                        <i class="ph ph-trash text-lg"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 italic">No se encontraron líneas telefónicas o terminales registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openCrear" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="openCrear = false" class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-700">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h4 class="font-black text-slate-800 text-sm uppercase tracking-tight flex items-center gap-2">
                    <i class="ph ph-device-mobile text-blue-600 text-lg"></i> Alta de Activo Celular Corporativo
                </h4>
                <button type="button" @click="openCrear = false" class="text-slate-400 hover:text-slate-600"><i class="ph ph-x text-xl"></i></button>
            </div>
            <form action="/planes-celulares/guardar" method="POST" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Número de Celular</label>
                        <input type="text" name="numero_celular" required placeholder="09xxxxxxxx" class="w-full border border-slate-200 p-2 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Operador</label>
                        <select name="operador" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none cursor-pointer">
                            <option value="Claro">Claro</option>
                            <option value="Movistar">Movistar</option>
                            <option value="CNT">CNT</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Costo Mensual</label>
                        <input type="number" step="0.01" name="costo_mensual" placeholder="0.00" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Nombre del Plan contratado</label>
                        <input type="text" name="nombre_plan" required placeholder="Pyme Corporativo Ilimitado" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Tipo de SIM</label>
                        <select name="tipo_sim" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none cursor-pointer">
                            <option value="Física (Nano-SIM)">Física (Nano-SIM)</option>
                            <option value="Virtual (eSIM)">Virtual (eSIM)</option>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100 my-2">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Marca / Proveedor</label>
                        <input type="text" name="celular_marca" required placeholder="Samsung, Apple, Xiaomi" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Modelo de Fábrica</label>
                        <input type="text" name="celular_modelo" required placeholder="Galaxy A54, iPhone 13" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">IMEI Principal (SIM 1)</label>
                        <input type="text" name="celular_imei_1" required maxlength="15" placeholder="15 dígitos numéricos" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">IMEI Secundario (SIM 2 / eSIM)</label>
                        <input type="text" name="celular_imei_2" maxlength="15" placeholder="Opcional" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Nº Serie Hardware</label>
                        <input type="text" name="celular_serie" placeholder="S/N de fábrica" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Almacenamiento</label>
                        <select name="celular_almacenamiento" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none cursor-pointer">
                            <option value="64GB">64GB</option>
                            <option value="128GB" selected>128GB</option>
                            <option value="256GB">256GB</option>
                            <option value="512GB">512GB</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Color Estético</label>
                        <input type="text" name="celular_color" required placeholder="Negro, Azul, Plata" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Salud Batería</label>
                        <input type="text" name="bateria_salud" value="100%" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none text-center">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Observaciones / Estado Inicial de Entrega</label>
                    <textarea name="observacion" rows="2" placeholder="Ej: Se ingresa con mica protectora de vidrio instalada y estuche transparente." class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="openCrear = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 font-bold rounded-xl text-xs transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 font-bold text-white rounded-xl text-xs transition-colors shadow-md">Dar de Alta Activo</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="openEditar" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="openEditar = false" class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-700">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h4 class="font-black text-slate-800 text-sm uppercase tracking-tight flex items-center gap-2">
                    <i class="ph ph-pencil-simple text-amber-600 text-lg"></i> Modificar Activo Celular Corporativo
                </h4>
                <button type="button" @click="openEditar = false" class="text-slate-400 hover:text-slate-600"><i class="ph ph-x text-xl"></i></button>
            </div>
            <form action="/planes-celulares/actualizar" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" x-model="editItem.id">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Número de Celular</label>
                        <input type="text" name="numero_celular" required x-model="editItem.numero_celular" class="w-full border border-slate-200 p-2 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Operador</label>
                        <select name="operador" x-model="editItem.operador" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none cursor-pointer">
                            <option value="Claro">Claro</option>
                            <option value="Movistar">Movistar</option>
                            <option value="CNT">CNT</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Costo Mensual</label>
                        <input type="number" step="0.01" name="costo_mensual" x-model="editItem.costo_mensual" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Nombre del Plan contratado</label>
                        <input type="text" name="nombre_plan" required x-model="editItem.nombre_plan" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Tipo de SIM</label>
                        <select name="tipo_sim" x-model="editItem.tipo_sim" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none cursor-pointer">
                            <option value="Física (Nano-SIM)">Física (Nano-SIM)</option>
                            <option value="Virtual (eSIM)">Virtual (eSIM)</option>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100 my-2">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Marca / Proveedor</label>
                        <input type="text" name="celular_marca" required x-model="editItem.celular_marca" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Modelo de Fábrica</label>
                        <input type="text" name="celular_modelo" required x-model="editItem.celular_modelo" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">IMEI Principal (SIM 1)</label>
                        <input type="text" name="celular_imei_1" required maxlength="15" x-model="editItem.celular_imei_1" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">IMEI Secundario (SIM 2 / eSIM)</label>
                        <input type="text" name="celular_imei_2" maxlength="15" x-model="editItem.celular_imei_2" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Nº Serie Hardware</label>
                        <input type="text" name="celular_serie" x-model="editItem.celular_serie" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Almacenamiento</label>
                        <select name="celular_almacenamiento" x-model="editItem.celular_almacenamiento" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none cursor-pointer">
                            <option value="64GB">64GB</option>
                            <option value="128GB">128GB</option>
                            <option value="256GB">256GB</option>
                            <option value="512GB">512GB</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Color Estético</label>
                        <input type="text" name="celular_color" required x-model="editItem.celular_color" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Salud Batería</label>
                        <input type="text" name="bateria_salud" x-model="editItem.bateria_salud" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Estado</label>
                        <select name="estado_plan" x-model="editItem.estado_plan" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none font-bold cursor-pointer">
                            <option value="Disponible">Disponible</option>
                            <option value="Asignado">Asignado</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Observaciones / Historial de Cambios</label>
                    <textarea name="observacion" rows="2" x-model="editItem.observacion" class="w-full border border-slate-200 p-2 rounded-xl text-xs outline-none"></textarea>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="openEditar = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 font-bold rounded-xl text-xs transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 font-bold text-white rounded-xl text-xs transition-colors shadow-md">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="selectItem" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="selectItem = null" class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden text-xs">
            <div class="p-4 bg-slate-900 text-white flex justify-between items-center">
                <span class="font-black uppercase tracking-wider">Ficha Técnica de Auditoría Móvil</span>
                <button type="button" @click="selectItem = null" class="text-slate-400 hover:text-white"><i class="ph ph-x text-lg"></i></button>
            </div>
            
            <div class="p-6 space-y-3 text-slate-700" x-show="selectItem">
                <div class="flex justify-between border-b pb-1.5 border-slate-100">
                    <span class="text-slate-400 font-bold">IMEI 1 (Principal):</span>
                    <span class="font-mono font-bold text-slate-900" x-text="selectItem ? selectItem.celular_imei_1 : ''"></span>
                </div>
                <div class="flex justify-between border-b pb-1.5 border-slate-100">
                    <span class="text-slate-400 font-bold">IMEI 2 (Dual SIM):</span>
                    <span class="font-mono text-slate-900" x-text="selectItem ? selectItem.celular_imei_2 : ''"></span>
                </div>
                <div class="flex justify-between border-b pb-1.5 border-slate-100">
                    <span class="text-slate-400 font-bold">Color Estético:</span>
                    <span class="font-bold text-slate-900 uppercase" x-text="selectItem ? selectItem.celular_color : ''"></span>
                </div>
                <div class="flex justify-between border-b pb-1.5 border-slate-100">
                    <span class="text-slate-400 font-bold">Salud de la Celda (Batería):</span>
                    <span class="font-bold text-emerald-600" x-text="selectItem ? selectItem.bateria_salud : ''"></span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 mt-2">
                    <span class="block font-bold text-[10px] text-slate-400 uppercase mb-1">Notas técnicas de ingreso:</span>
                    <p class="text-slate-600 italic leading-relaxed" x-text="selectItem && selectItem.observacion ? selectItem.observacion : 'Sin observaciones detalladas.'"></p>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/js/planes.js"></script>
