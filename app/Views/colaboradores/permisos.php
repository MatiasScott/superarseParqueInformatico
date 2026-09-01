<?php
$usuarios = $usuarios ?? [];
$usuarios = array_map(function ($usuario) {
    $usuario['permisos'] = !empty($usuario['permisos']) ? json_decode($usuario['permisos'], true) : [];
    return $usuario;
}, $usuarios);

$permissionSchema = [
    [
        'key' => 'equipos',
        'label' => 'Equipos de Cómputo',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
        ]
    ],
    [
        'key' => 'componentes',
        'label' => 'Componentes',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
        ]
    ],
    [
        'key' => 'planes_celulares',
        'label' => 'Planes Celulares',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
        ]
    ],
    [
        'key' => 'asignaciones',
        'label' => 'Asignaciones',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
            ['key' => 'recibir', 'label' => 'Recibir'],
        ]
    ],
    // --- REPORTES NUEVOS ---
    [
        'key' => 'equipos-asignados',
        'label' => 'Equipos Asignados (Reporte)',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver Acceso'],
        ]
    ],
    [
        'key' => 'equipos-baja',
        'label' => 'Bajas Tecnológicas (Reporte)',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver Acceso'],
        ]
    ],
    [
        'key' => 'documentos',
        'label' => 'Documentos',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
        ]
    ],
    [
        'key' => 'inventario',
        'label' => 'Inventario',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
        ]
    ],
    [
        'key' => 'mantenimientos',
        'label' => 'Órdenes de Soporte',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
        ]
    ],
    [
        'key' => 'estadisticas',
        'label' => 'Estadísticas',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
        ]
    ],
    [
        'key' => 'auditoria',
        'label' => 'Auditoría',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
        ]
    ],
    [
        'key' => 'colaboradores',
        'label' => 'Colaboradores',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'editar', 'label' => 'Editar'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
        ]
    ],
    [
        'key' => 'usuarios',
        'label' => 'Usuarios',
        'actions' => [
            ['key' => 'ver', 'label' => 'Ver'],
            ['key' => 'crear', 'label' => 'Crear'],
            ['key' => 'eliminar', 'label' => 'Eliminar'],
            ['key' => 'permisos', 'label' => 'Permisos'],
        ]
    ],
];
?>

<div class="col-span-3" x-data="permisosComponent()" x-init="init()">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Panel de Permisos</h2>
            <p class="text-slate-500 mt-1">Asigna accesos por módulos y acciones a cada usuario.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
            Permisos guardados correctamente.
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <div class="grid gap-4 lg:grid-cols-2">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Usuario</label>
                <select x-model="selectedUserId" @change="loadPermissions()"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <template x-for="user in users" :key="user.id">
                        <option :value="user.id" x-text="user.nombre + ' (' + user.email + ')'"> </option>
                    </template>
                </select>
            </div>

            <div>
                <p class="text-sm font-bold text-slate-700 mb-2">Instrucciones</p>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    Selecciona un usuario y activa las casillas para concederle acceso a cada módulo y acción. Los cambios se aplican al guardar.
                </div>
            </div>
        </div>
    </div>

    <!-- Modificamos el envío para generar de forma segura los inputs nativos que PHP lee de forma clásica -->
    <form x-ref="permisosForm" action="/usuarios/permisos/guardar" method="POST" class="space-y-6" @submit.prevent="submitForm">
        <input type="hidden" name="user_id" :value="selectedUserId">
        
        <!-- Este contenedor dinámico recibirá los inputs ocultos temporales con el formato de array que tu controlador espera -->
        <div x-ref="hiddenInputsContainer"></div>

        <template x-for="section in sections" :key="section.key">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800" x-text="section.label"></h3>
                        <p class="text-sm text-slate-500 mt-1">Permisos disponibles para esta sección.</p>
                    </div>
                    <div class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">
                        Sección: <span x-text="section.key.replace('_', ' ').replace('-', ' ')" class="capitalize"></span>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <template x-for="action in section.actions" :key="action.key">
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer transition hover:border-blue-300">
                            <!-- Quitamos el atributo :name de aquí para que no interfiera. Alpine lo procesa directamente -->
                            <input type="checkbox"
                                   x-model="permisos[section.key][action.key]"
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <span class="font-semibold text-slate-700" x-text="action.label"></span>
                        </label>
                    </template>
                </div>
            </div>
        </template>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button type="button" @click="loadPermissions()"
                    class="px-6 py-3 rounded-2xl border border-slate-200 bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition">
                Recargar permisos
            </button>
            <button type="submit"
                    class="px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition">
                Guardar permisos
            </button>
        </div>
    </form>
</div>

<script>
function permisosComponent() {
    return {
        users: <?= json_encode($usuarios) ?>,
        sections: <?= json_encode($permissionSchema) ?>,
        selectedUserId: <?= count($usuarios) ? (int)$usuarios[0]['id'] : 'null' ?>,
        permisos: {},

        init() {
            if (!this.selectedUserId && this.users.length) {
                this.selectedUserId = this.users[0].id;
            }
            this.loadPermissions();
        },

        loadPermissions() {
            const user = this.users.find(u => u.id == this.selectedUserId);
            this.permisos = {};

            if (!user) {
                return;
            }

            const raw = user.permisos || {};
            this.sections.forEach(section => {
                this.permisos[section.key] = {};
                section.actions.forEach(action => {
                    const val = raw[section.key] && raw[section.key][action.key];
                    // Normalización estricta a Booleano para Alpine
                    this.permisos[section.key][action.key] = (val === true || val == 1 || val === "1");
                });
            });
        },

        submitForm() {
            const form = this.$refs.permisosForm;
            const container = this.$refs.hiddenInputsContainer;
            const user = this.users.find(u => u.id == this.selectedUserId);
            
            if (!user) return;

            // Limpiamos los inputs temporales anteriores si los hubiera
            container.innerHTML = '';

            // Recorremos el objeto reactivo de Alpine y generamos inputs reales en el DOM justo antes del envío
            Object.keys(this.permisos).forEach(sectionKey => {
                Object.keys(this.permisos[sectionKey]).forEach(actionKey => {
                    const isChecked = this.permisos[sectionKey][actionKey];
                    
                    if (isChecked) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        // El nombre exacto que PHP procesará como array de forma nativa
                        input.name = `permisos[${sectionKey}][${actionKey}]`;
                        input.value = '1';
                        container.appendChild(input);
                    }
                });
            });

            // Una vez inyectados los inputs nativos con sus valores reales, procedemos al submit
            form.submit();
        }
    }
}
</script>