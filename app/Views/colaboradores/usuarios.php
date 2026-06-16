<?php
$usuarios = $usuarios ?? [];
$totalUsuarios = $totalUsuarios ?? 0;
$canCreateUsuarios = sessionHasPermission('usuarios', 'crear');
$canEditUsuarios = sessionHasPermission('usuarios', 'editar');
$canDeleteUsuarios = sessionHasPermission('usuarios', 'eliminar');
?>

<div class="col-span-3" x-data="moduloUsuariosComponent()">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Gestión de Usuarios</h2>
            <p class="text-slate-500 mt-1">Control de accesos y roles del sistema institucional</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-2xl shadow-sm">
                <p class="text-xs uppercase tracking-wider text-slate-400">Total</p>
                <h3 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($totalUsuarios) ?></h3>
            </div>

            <?php if ($canCreateUsuarios): ?>
            <button @click="abrirModalCrear()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                <i class="ph ph-user-plus text-xl"></i>
                Nuevo Usuario
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
        <div class="relative">
            <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Buscar por nombre, correo o rol..."
                class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="p-4">Usuario</th>
                        <th class="p-4">Correo Electrónico</th>
                        <th class="p-4">Rol de Acceso</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Primer Inicio</th>
                        <th class="p-4">Fecha Registro</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="u in filteredUsuarios" :key="u.id">
                        <tr class="hover:bg-slate-50 transition text-sm text-slate-700">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold"
                                         x-text="obtenerIniciales(u.nombre)">
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800" x-text="u.nombre"></p>
                                        <p class="text-xs text-slate-400" x-text="'ID #' + u.id"></p>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="p-4 font-medium text-slate-600" x-text="u.email"></td>
                            
                            <td class="p-4">
                                <span :class="{
                                    'bg-purple-50 text-purple-600 border border-purple-100': u.rol === 'admin',
                                    'bg-sky-50 text-sky-600 border border-sky-100': u.rol === 'tecnico',
                                    'bg-slate-50 text-slate-600 border border-slate-200': u.rol === 'usuario'
                                }" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border" x-text="u.rol">
                                </span>
                            </td>

                            <td class="p-4">
                                <span :class="u.estado == 1 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'"
                                      class="px-2.5 py-0.5 text-xs font-bold rounded-md border"
                                      x-text="u.estado == 1 ? 'Activo' : 'Inactivo'">
                                </span>
                            </td>

                            <td class="p-4">
                                <span :class="u.primer_inicio == 1 ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-50 text-slate-600 border border-slate-200'"
                                      class="px-2.5 py-0.5 text-xs font-bold rounded-md border"
                                      x-text="u.primer_inicio == 1 ? 'Pendiente' : 'Completado'">
                                </span>
                            </td>
                            
                            <td class="p-4 text-slate-400 text-xs" x-text="formatearFecha(u.created_at)"></td>
                            
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                <?php if ($canEditUsuarios): ?>
                                    <button @click="abrirModalEditar(u)"
                                        class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Editar">
                                        <i class="ph ph-pencil-line text-lg"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($canDeleteUsuarios): ?>
                                <template x-if="u.id != 3">
                                    <a :href="'/usuarios/eliminar?id=' + u.id"
                                       onclick="return confirm('¿Está seguro de revocar permanentemente los accesos a este usuario del sistema?')"
                                       class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Eliminar">
                                        <i class="ph ph-trash text-lg"></i>
                                    </a>
                                </template>
                                <?php endif; ?>
                            </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredUsuarios.length === 0" x-cloak>
                        <td colspan="6" class="p-10 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-slate-100 p-5 rounded-full mb-4">
                                    <i class="ph ph-users text-5xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700">No hay usuarios que coincidan con la búsqueda</h3>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false" x-transition>
        
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8" @click.away="modalOpen = false">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-slate-800" x-text="isEdit ? 'Editar Usuario' : 'Registrar Usuario'"></h3>
                    <p class="text-slate-500 text-sm mt-1" x-text="isEdit ? 'Modifique los accesos de la cuenta seleccionada' : 'Cree una cuenta con privilegios específicos'"></p>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-red-500 text-2xl">
                    <i class="ph ph-x"></i>
                </button>
            </div>

            <form action="/usuarios/guardar" method="POST" class="space-y-4">
                <input type="hidden" name="id" x-model="formData.id">

                <div>
                    <label class="text-sm font-bold text-slate-700">Nombre Completo</label>
                    <input type="text" name="nombre" required x-model="formData.nombre" 
                           class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700">Correo Electrónico</label>
                    <input type="email" name="email" required x-model="formData.email" 
                           class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700">Contraseña</label>
                    <input type="password" name="password" :required="!isEdit" placeholder="••••••••"
                           class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <p class="text-xs text-amber-500 mt-1" x-show="isEdit" x-cloak>Dejar en blanco para mantener la contraseña actual.</p>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700">Rol Asignado</label>
                    <select name="rol" x-model="formData.rol" required 
                            class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div x-show="isEdit" x-cloak>
                    <label class="text-sm font-bold text-slate-700">Estado de la Cuenta</label>
                    <select name="estado" x-model="formData.estado" 
                            class="w-full mt-1 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo (Acceso Suspendido)</option>
                    </select>
                </div>

                <div x-show="isEdit" x-cloak>
                    <input type="hidden" name="primer_inicio" value="0">
                    <label class="flex items-center gap-3 mt-2">
                        <input type="checkbox" name="primer_inicio" value="1" :checked="formData.primer_inicio === '1'" 
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-slate-700">Requerir primer inicio de sesión</span>
                    </label>
                    <p class="text-xs text-slate-500 mt-1">Obliga al usuario a cambiar su contraseña en el siguiente ingreso.</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="modalOpen = false" 
                            class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                        Guardar Cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function moduloUsuariosComponent() {
    return {
        search: '',
        modalOpen: false,
        isEdit: false,
        usuarios: <?= json_encode($usuarios) ?>,
        formData: { id: '', nombre: '', email: '', rol: 'usuario', estado: '1', primer_inicio: '0' },

        get filteredUsuarios() {
            return this.usuarios.filter(u => {
                const query = this.search.toLowerCase();
                return (u.nombre || '').toLowerCase().includes(query) ||
                       (u.email || '').toLowerCase().includes(query) ||
                       (u.rol || '').toLowerCase().includes(query);
            });
        },

        abrirModalCrear() {
            this.isEdit = false;
            this.formData = { id: '', nombre: '', email: '', rol: 'usuario', estado: '1' };
            this.modalOpen = true;
        },

        abrirModalEditar(usuario) {
            this.isEdit = true;
            this.formData = {
                id: usuario.id,
                nombre: usuario.nombre,
                email: usuario.email,
                rol: usuario.rol,
                estado: usuario.estado.toString(),
                primer_inicio: usuario.primer_inicio && usuario.primer_inicio == 1 ? '1' : '0'
            };
            this.modalOpen = true;
        },

        obtenerIniciales(nombre) {
            if (!nombre) return 'US';
            const palabras = nombre.trim().split(' ');
            if (palabras.length >= 2) {
                return (palabras[0][0] + palabras[1][0]).toUpperCase();
            }
            return palabras[0].substring(0, 2).toUpperCase();
        },

        formatearFecha(fechaRaw) {
            if (!fechaRaw) return 'N/A';
            // Formateador rápido para transformaciones de marcas temporales de BD
            const partes = fechaRaw.split(' ');
            if (partes.length < 1) return fechaRaw;
            const f = partes[0].split('-');
            if (f.length !== 3) return fechaRaw;
            const hora = partes[1] ? partes[1].substring(0, 5) : '';
            return `${f[2]}/${f[1]}/${f[0]} ${hora}`;
        }
    }
}
</script>