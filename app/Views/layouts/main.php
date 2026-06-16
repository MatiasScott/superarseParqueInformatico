<?php 
  // 1. Sincronización horaria con la base de datos (America/Bogota)
  date_default_timezone_set('America/Bogota'); 

  // 2. Control de sesión y sanitización de página activa para evitar Notices en PHP
  if (session_status() === PHP_SESSION_NONE) { session_start(); }
  $activePage = (isset($activePage) && !empty($activePage)) ? $activePage : 'dashboard'; 
  $content = $content ?? '<p class="text-slate-500">No hay contenido disponible para esta vista.</p>';

  // 3. Mapeo exacto de identificadores de páginas (¡Totalmente unificado con las rutas del menú!)
  $pageTitles = [
      'dashboard'         => 'Panel de Control e Indicadores',
      'equipos'           => 'Inventario de Hardware y Activos',
      'componentes'       => 'Desglose de Componentes Internos',
      'planes-celulares'  => 'Inventario Global de Telefonía y Líneas Móviles',
      'mantenimientos'    => 'Órdenes de Soporte Técnico',
      'colaboradores'     => 'Fichas de Colaboradores / Personal',
      'asignaciones'      => 'Gestión de Actas de Asignación (Maestro)',
      'documentos'        => 'Repositorio Digital de Adjuntos',
      'inventario'        => 'Reporte: Consolidado de Inventario General',
      'equipos-asignados' => 'Reporte: Equipos en Uso Operativo',
      'equipos-baja'      => 'Reporte: Histórico de Bajas y Pérdidas',
      'usuarios'          => 'Control de Acceso y Usuarios',
      'usuarios_permisos'  => 'Control de Permisos por Usuario',
      'cambiar-contrasena' => 'Cambio de contraseña obligatorio',
      'estadisticas'      => 'Análisis Estadístico y Gráficos',
      'auditoria'         => 'Bitácora de Auditoría Forense (JSON Logs)'
  ];

  $currentTitle = $pageTitles[$activePage] ?? 'Sistema de Gestión TIC';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($currentTitle) ?> | InfoPark</title>
    <link rel="icon" type="image/png" href="/assets/img/infopark02.png"/>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.APP_BASE_PATH = <?= json_encode(defined('BASE_PATH') ? BASE_PATH : '') ?>;
    </script>
    <link rel="stylesheet" href="/css/style.css">

    
</head>

<body class="bg-slate-100 h-full overflow-hidden text-slate-800 flex font-sans" x-data="{ sideBarOpen: false }">

    <div x-show="sideBarOpen"
         x-transition.opacity
         @click="sideBarOpen = false"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden"
         x-cloak>
    </div>

    <aside
        :class="sideBarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="infopark-aside-bg fixed inset-y-0 left-0 z-50 w-72 text-slate-700 flex flex-col h-full border-r border-slate-200/80 shadow-xl transition-transform duration-300 lg:translate-x-0 lg:static lg:h-full flex-shrink-0">
        
        <div class="p-6 flex items-center justify-between flex-shrink-0 border-b border-slate-200/60 relative bg-white/60 backdrop-blur-md">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-blue-600 to-orange-500"></div>
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center justify-center w-40 h-12 overflow-hidden bg-transparent">
                    <img src="/assets/img/infopark01.png" 
                         alt="Vista del Programa InfoPark" 
                         class="w-full h-full object-contain">
                </div>
            </div>
            <button @click="sideBarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 transition-colors">
                <i class="ph ph-x text-2xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-5">
            
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 px-3">Monitoreo</p>
                <div class="space-y-1">
                    <a href="/dashboard"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'dashboard') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-chart-pie-slice text-lg <?= ($activePage == 'dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Dashboard Global</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 px-3">Activos e Inventario</p>
                <div class="space-y-1">
                    <a href="/equipos"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'equipos') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-desktop text-lg <?= ($activePage == 'equipos') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Equipos de Cómputo</span>
                    </a>
                    <a href="/componentes"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'componentes') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-hard-drives text-lg <?= ($activePage == 'componentes') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Componentes de Equipo</span>
                    </a>
                    <a href="/planes-celulares"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'planes-celulares') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-device-mobile text-lg <?= ($activePage == 'planes-celulares') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Planes y Celulares</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 px-3">Operaciones</p>
                <div class="space-y-1">
                    <a href="/colaboradores"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'colaboradores') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-users-three text-lg <?= ($activePage == 'colaboradores') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Colaboradores</span>
                    </a>
                    <a href="/asignaciones"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'asignaciones') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-textbox text-lg <?= ($activePage == 'asignaciones') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Actas de Asignación</span>
                    </a>
                    <a href="/mantenimientos"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'mantenimientos') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-wrench text-lg <?= ($activePage == 'mantenimientos') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Órdenes de Soporte</span>
                    </a>
                    <a href="/documentos"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'documentos') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-file-pdf text-lg <?= ($activePage == 'documentos') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Repositorio Digital</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 px-3">Módulo de Reportes</p>
                <div class="space-y-1">
                    <a href="/inventario"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'inventario') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-file-xls text-lg <?= ($activePage == 'inventario') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Inventario General</span>
                    </a>
                    <a href="/equipos-asignados"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'equipos-asignados') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-monitor-arrow-up text-lg <?= ($activePage == 'equipos-asignados') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Equipos Asignados</span>
                    </a>
                    <a href="/equipos-baja"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'equipos-baja') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-trash text-lg <?= ($activePage == 'equipos-baja') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Bajas Tecnológicas</span>
                    </a>
                    <a href="/estadisticas"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'estadisticas') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-chart-bar text-lg <?= ($activePage == 'estadisticas') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Estadísticas y Gráficos</span>
                    </a>
                </div>
            </div>

            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 px-3">Security y Logs</p>
                <div class="space-y-1">
                    <a href="/usuarios"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'usuarios') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-user-gear text-lg <?= ($activePage == 'usuarios') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Gestión de Usuarios</span>
                    </a>
                    <a href="/usuarios/permisos"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'usuarios_permisos') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-shield-check text-lg <?= ($activePage == 'usuarios_permisos') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Permisos de Acceso</span>
                    </a>
                    <a href="/auditoria"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group
                       <?= ($activePage == 'auditoria') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 font-bold' : 'text-slate-600 hover:bg-sky-500/10 hover:text-sky-700' ?>">
                        <i class="ph ph-shield-check text-lg <?= ($activePage == 'auditoria') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' ?>"></i>
                        <span>Bitácora de Auditoría</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </nav>

        <div class="p-4 border-t border-slate-200/60 flex-shrink-0 bg-white/40">
            <div class="bg-white/80 border border-slate-200 rounded-2xl p-3.5 shadow-sm">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nombre'] ?? 'Usuario') ?>&background=0052cc&color=fff&bold=true"
                         class="w-10 h-10 rounded-xl shadow-sm border border-slate-100"
                         alt="avatar-usuario">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Alexander Admin') ?></p>
                        <p class="text-blue-600 text-[9px] uppercase tracking-[0.15em] font-black mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? 'admin')) ?></p>
                    </div>
                </div>
                <a href="/logout"
                   class="mt-3 flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-600 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 border border-rose-100/50 shadow-sm">
                    <i class="ph ph-sign-out"></i>
                    Cerrar Sesión Interna
                </a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc]">
        
        <header class="bg-white border-b border-slate-200/80 px-6 py-4 flex-shrink-0 shadow-sm z-30">
            <div class="flex items-center justify-between">
                
                <div class="flex items-center gap-4">
                    <button @click="sideBarOpen = true"
                            class="lg:hidden p-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                        <i class="ph ph-list text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 tracking-tight leading-none">
                            <?= htmlspecialchars($currentTitle) ?>
                        </h2>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1.5">
                            <span class="flex h-2 w-2 relative">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Servidor Conectado
                        </div>
                    </div>
                </div>
                
                <div class="hidden md:block text-right bg-slate-50 border border-slate-200/60 px-4 py-1.5 rounded-xl" 
                     x-data="{ 
                        hora: '<?= date('H:i') ?>',
                        init() {
                            let [h, m] = this.hora.split(':').map(Number);
                            setInterval(() => {
                                m++;
                                if (m >= 60) { m = 0; h++; }
                                if (h >= 24) { h = 0; }
                                this.hora = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                            }, 60000);
                        }
                     }">
                    <p class="text-xs font-black text-slate-700 tracking-tight"><?= date('d/m/Y') ?></p>
                    <p class="text-[10px] font-mono font-bold text-slate-400 mt-0.5" x-text="hora + ' COT'"></p>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="max-w-7xl mx-auto">
                <?= $content ?>
            </div>
        </main>

        <footer class="bg-white border-t border-slate-200 px-8 py-3 text-center lg:text-left lg:flex lg:justify-between flex-shrink-0 z-30">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                © <?= date('Y') ?> InfoPark | Instituto Superarse
            </p>
        </footer>
    </div>

</body>
</html>