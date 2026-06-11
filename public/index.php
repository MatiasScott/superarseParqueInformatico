<?php

// ===============================
// DEFINICIÓN DE RUTAS (BASE)
// ===============================
// Esto apunta a la carpeta /app/ desde cualquier lugar
define('ROOT', dirname(__DIR__) . '/app/');

// ===============================
// HELPERS
// ===============================
require_once ROOT . 'Helpers/database.php';
require_once ROOT . 'Helpers/permissions.php';


// ===============================
// MODELS
// ===============================
require_once ROOT . 'Models/Usuarios/Usuario.php';
require_once ROOT . 'Models/Colaboradores/ColaboradorModel.php';
require_once ROOT . 'Models/Dashboard/Dashboard.php';
require_once ROOT . 'Models/Equipos/Equipo.php';
require_once ROOT . 'Models/Componentes/ComponenteModel.php';
require_once ROOT . 'Models/Mantenimientos/MantenimientoModel.php';
require_once ROOT . 'Models/Asignaciones/AsignacionesModel.php';
require_once ROOT . 'Models/Documentos/DocumentosModel.php';
require_once ROOT . 'Models/Inventario/InventarioModel.php';
require_once ROOT . 'Models/Asignaciones/EquiposAsignadosModel.php';
require_once ROOT . 'Models/Bajas/BajasModel.php';
require_once ROOT . 'Models/Equipos/HistorialModel.php';
require_once ROOT . 'Models/Colaboradores/UsuariosModel.php';
require_once ROOT . 'Models/Auditoria/AuditoriaModel.php';
require_once ROOT . 'Models/Asignaciones/AsignacionModel.php';
require_once ROOT . 'Models/Celular/PlanesCelularesModel.php';
require_once ROOT . 'Models/Estadistica/EstadisticaModel.php';



// ===============================
// CONTROLLERS
// ===============================
require_once ROOT . 'Controllers/Usuarios/UsuarioController.php';
require_once ROOT . 'Controllers/Colaboradores/ColaboradorController.php';
require_once ROOT . 'Controllers/Dashboard/DashboardController.php';
require_once ROOT . 'Controllers/Equipos/EquipoController.php';
require_once ROOT . 'Controllers/Componentes/ComponenteController.php';
require_once ROOT . 'Controllers/Mantenimientos/MantenimientoController.php';
require_once ROOT . 'Controllers/Asignaciones/AsignacionesController.php';
require_once ROOT . 'Controllers/Documentos/DocumentosController.php'; // Corregido el nombre si aplica
require_once ROOT . 'Controllers/Inventario/InventarioController.php';
require_once ROOT . 'Controllers/Asignaciones/EquiposAsignadosController.php';
require_once ROOT . 'Controllers/Bajas/BajasController.php'; // Ajustado según tu controlador
require_once ROOT . 'Controllers/Equipos/HistorialController.php';
require_once ROOT . 'Controllers/Colaboradores/UsuariosController.php';
require_once ROOT . 'Controllers/Auditoria/AuditoriaController.php';
require_once ROOT . 'Controllers/Asignaciones/AsignacionController.php';
require_once ROOT . 'Controllers/Celular/PlanesCelularesController.php';
require_once ROOT . 'Controllers/Estadistica/EstadisticasController.php';

// ===============================
// SESSION
// ===============================
session_start();

// Refresca permisos en sesión en cada request para mantener los permisos actuales.
if (!empty($_SESSION['user_id'])) {
    $currentUserModel = new \App\Models\Colaboradores\UsuariosModel();
    $currentUser = $currentUserModel->find((int)$_SESSION['user_id']);
    if ($currentUser) {
        $_SESSION['permisos'] = !empty($currentUser['permisos']) ? json_decode($currentUser['permisos'], true) : [];
    }
}

// ===============================
// BASE PATH
// ===============================
// Asegúrate de que este nombre coincida exactamente con tu carpeta en htdocs
$base_path = '/superarseParqueInformatico/public';


// ===============================
// URL ACTUAL
// ===============================
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = str_replace($base_path, '', $request_uri);


// ===============================
// RUTA PRINCIPAL
// ===============================
if ($route == '' || $route == '/') {
    $route = '/login';
}


// ===============================
// ROUTER
// ===============================
switch ($route) {

    // ===========================
    // LOGIN
    // ===========================
    case '/login':
        if (isset($_SESSION['user_id'])) {
            if (!empty($_SESSION['force_password_change'])) {
                header("Location: " . $base_path . "/cambiar-contrasena");
                exit();
            }
            header("Location: " . $base_path . "/dashboard");
            exit();
        }
        require_once ROOT . 'Views/login.php';
        break;


    // ===========================
    // AUTENTICAR
    // ===========================
    case '/login/autenticar':
        (new \App\Controllers\Usuarios\UsuarioController())->autenticar();
        break;

    case '/cambiar-contrasena':
        validarSesion();
        (new \App\Controllers\Usuarios\UsuarioController())->mostrarCambiarContrasena();
        break;

    case '/cambiar-contrasena/guardar':
        validarSesion();
        (new \App\Controllers\Usuarios\UsuarioController())->guardarCambiarContrasena();
        break;


    // ===========================
    // DASHBOARD
    // ===========================
    case '/dashboard':
        validarSesion();
        (new \App\Controllers\Dashboard\DashboardController())->index();
        break;

    // ===========================
    // USUARIOS (SISTEMA ANTERIOR O REGISTRO EXTERNO)
    // ===========================
    case '/usuarios/crear':
        validarSesion();
        (new \App\Controllers\Usuarios\UsuarioController())->crear();
        break;

    // ===========================
    // NUEVA GESTIÓN DE USUARIOS / ROLES (BAJO COLABORADORES)
    // ===========================
    case '/usuarios':
        validarSesion();
        (new \App\Controllers\Colaboradores\UsuariosController())->index();
        break;

    case '/usuarios/guardar':
        validarSesion();
        (new \App\Controllers\Colaboradores\UsuariosController())->guardar();
        break;

    case '/usuarios/eliminar':
        validarSesion();
        (new \App\Controllers\Colaboradores\UsuariosController())->eliminar();
        break;

    case '/usuarios/permisos':
        validarSesion();
        (new \App\Controllers\Colaboradores\UsuariosController())->permisos();
        break;

    case '/usuarios/permisos/guardar':
        validarSesion();
        (new \App\Controllers\Colaboradores\UsuariosController())->guardarPermisos();
        break;


    // ===========================
    // COLABORADORES
    // ===========================
    case '/colaboradores':
        validarSesion();
        (new \App\Controllers\Colaboradores\ColaboradorController())->index();
        break;

    case '/colaboradores/guardar':
        validarSesion();
        (new \App\Controllers\Colaboradores\ColaboradorController())->guardar();
        break;

    case '/colaboradores/editar':
        validarSesion();
        (new \App\Controllers\Colaboradores\ColaboradorController())->editar();
        break;

    case '/colaboradores/actualizar':
        validarSesion();
        (new \App\Controllers\Colaboradores\ColaboradorController())->actualizar();
        break;

    case '/colaboradores/eliminar':
        validarSesion();
        (new \App\Controllers\Colaboradores\ColaboradorController())->eliminar();
        break;
    
    // ===========================
    // EQUIPOS
    // ===========================
    case '/equipos':
        validarSesion();
        (new \App\Controllers\Equipos\EquipoController())->index();
        break;

    case '/equipos/guardar':
        validarSesion();
        (new \App\Controllers\Equipos\EquipoController())->guardar();
        break;

    case '/equipos/editar':
        validarSesion();
        (new \App\Controllers\Equipos\EquipoController())->editar();
        break;

    case '/equipos/actualizar':
        validarSesion();
        (new \App\Controllers\Equipos\EquipoController())->actualizar();
        break;

    case '/equipos/eliminar':
        validarSesion();
        (new \App\Controllers\Equipos\EquipoController())->eliminar();
        break;




    // ================================================
    // Planes Celulares 
    // ================================================

   case '/planes-celulares':
    validarSesion();
    (new \App\Controllers\Celular\PlanesCelularesController())->index();
    break;

    case '/planes-celulares/guardar':
    validarSesion();
    (new \App\Controllers\Celular\PlanesCelularesController())->store();
    break;

    case '/planes-celulares/eliminar':
    validarSesion();
    (new \App\Controllers\Celular\PlanesCelularesController())->destroy();
    break;
    case '/planes-celulares/editar':
    validarSesion();
    (new \App\Controllers\Celular\PlanesCelularesController())->edit();
    break;

case '/planes-celulares/actualizar':
    validarSesion();
    (new \App\Controllers\Celular\PlanesCelularesController())->update();
    break;

    // ===========================
    // HISTORIAL GENERAL DE EQUIPOS 
    // ===========================
    case '/historial':
        validarSesion();
        (new \App\Controllers\Equipos\HistorialController())->index();
        break;


    // ===========================
    // MANTENIMIENTOS
    // ===========================
    case '/mantenimientos':
        validarSesion();
        (new \App\Controllers\Mantenimientos\MantenimientoController())->index();
        break;

    case '/mantenimientos/guardar':
        validarSesion();
        (new \App\Controllers\Mantenimientos\MantenimientoController())->guardar();
        break;

    case '/mantenimientos/editar':
        validarSesion();
        (new \App\Controllers\Mantenimientos\MantenimientoController())->editar();
        break;

    case '/mantenimientos/actualizar':
        validarSesion();
        (new \App\Controllers\Mantenimientos\MantenimientoController())->actualizar();
        break;

    case '/mantenimientos/eliminar':
        validarSesion();
        (new \App\Controllers\Mantenimientos\MantenimientoController())->eliminar();
        break;
   
       
    // ===========================
    // ASIGNACIONES
    // ===========================
  case '/asignaciones':
        validarSesion();
        (new \App\Controllers\AsignacionesController())->index();
        break;

    case '/asignaciones/guardar':
        validarSesion();
        (new \App\Controllers\AsignacionesController())->guardar();
        break;

    case '/asignaciones/ver':
        validarSesion();
        (new \App\Controllers\AsignacionesController())->ver();
        break;

    case '/asignaciones/devolverEquipo':
        validarSesion();
        (new \App\Controllers\AsignacionesController())->devolverEquipo();
        break;

    case '/asignaciones/eliminar':
        validarSesion();
        (new \App\Controllers\AsignacionesController())->eliminar();
        break;

    // ================================================
    // Imprimir formato de acta de asiganación 
    // ================================================

    case '/asignaciones/imprimirActa':
    validarSesion();
    (new \App\Controllers\AsignacionController())->imprimirActa(); // 👈 Quitamos "\Asignaciones"
    break;

    // =============================================================
    // MÓDULO DE COMPONENTES (CON TODAS SUS RUTAS)
    // =============================================================
    case '/componentes':
        validarSesion();
        (new \App\Controllers\Componentes\ComponenteController())->index();
        break;

    case '/componentes/guardar':
        validarSesion();
        (new \App\Controllers\Componentes\ComponenteController())->guardar();
        break;

    case '/componentes/editar':
        validarSesion();
        (new \App\Controllers\Componentes\ComponenteController())->editar();
        break;

    // ⬇️ ESTA ES LA QUE PROCESA LOS CAMBIOS EN LA BASE DE DATOS ⬇️
    case '/componentes/actualizar':
        validarSesion();
        (new \App\Controllers\Componentes\ComponenteController())->actualizar();
        break;

    // ⬇️ ESTA ES LA QUE BORRA EL COMPONENTE ⬇️
    case '/componentes/eliminar':
        validarSesion();
        (new \App\Controllers\Componentes\ComponenteController())->eliminar();
        break;


    // ===========================
    // DOCUMENTOS
    // ===========================
    case '/documentos':
        validarSesion();
        (new \App\Controllers\Documentos\DocumentoController())->index();
        break;
    case '/documentos/guardar':
        validarSesion();
        (new \App\Controllers\Documentos\DocumentoController())->guardar();
        break;
    case '/documentos/actualizar':
        validarSesion();
        (new \App\Controllers\Documentos\DocumentoController())->actualizar();
        break;
    case '/documentos/eliminar':
        validarSesion();
        (new \App\Controllers\Documentos\DocumentoController())->eliminar();
        break;

    // ===========================
    // INVENTARIO GENERAL
    // ===========================
    case '/inventario':
        validarSesion();
        (new \App\Controllers\Inventario\InventarioController())->index();
        break;

    // ===========================
    // EQUIPOS ASIGNADOS 
    // ===========================
   case '/equipos-asignados':
    validarSesion();
    (new \App\Controllers\Inventario\EquiposAsignadosController())->index();
    break;
        

    // ===========================
    // EQUIPOS DADOS DE BAJA
    // ===========================
    case '/equipos-baja':
        validarSesion();
        (new \App\Controllers\Equipos\BajasController())->index();
        break;
    

    // ===========================
    // ESTADISTICAS Y GRÁFICOS
    // ===========================
    case '/estadisticas':
    validarSesion();
    (new \App\Controllers\Estadistica\EstadisticasController())->index();
    break;

case '/estadisticas/imprimir': // 🖨️ Nueva ruta para el reporte PDF
    validarSesion();
    (new \App\Controllers\Estadistica\EstadisticasController())->exportarPdf();
    break;
    // ===========================
    // AUDITORÍA
    // ===========================
    case '/auditoria':
        validarSesion();
        (new \App\Controllers\Auditoria\AuditoriaController())->index();
        break;
  

    // ===========================
    // LOGOUT
    // ===========================
    case '/logout':
        session_destroy();
        header("Location: " . $base_path . "/login");
        exit();


    // ===========================
    // 404
    // ===========================
    default:
        http_response_code(404);
        echo "
        <div style='font-family: Arial; padding: 40px; text-align:center;'>
            <h1>404</h1>
            <p>La ruta <b>{$route}</b> no existe en el sistema.</p>
            <a href='{$base_path}/dashboard'>Volver al inicio</a>
        </div>
        ";
        break;
}


// ===============================
// VALIDAR SESIÓN
// ===============================
function validarSesion()
{
    global $base_path, $route;

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $protectedRoutes = ['/cambiar-contrasena', '/cambiar-contrasena/guardar'];
    if (!empty($_SESSION['force_password_change']) && !in_array($route, $protectedRoutes, true)) {
        header("Location: " . $base_path . "/cambiar-contrasena");
        exit();
    }

    if (!empty($_SESSION['rol']) && $_SESSION['rol'] !== 'admin') {
        $routePermissions = [
            '/equipos' => ['module' => 'equipos', 'action' => 'ver'],
            '/equipos/guardar' => ['module' => 'equipos', 'action' => 'crear'],
            '/equipos/editar' => ['module' => 'equipos', 'action' => 'editar'],
            '/equipos/actualizar' => ['module' => 'equipos', 'action' => 'editar'],
            '/equipos/eliminar' => ['module' => 'equipos', 'action' => 'eliminar'],

            '/componentes' => ['module' => 'componentes', 'action' => 'ver'],
            '/componentes/guardar' => ['module' => 'componentes', 'action' => 'crear'],
            '/componentes/editar' => ['module' => 'componentes', 'action' => 'editar'],
            '/componentes/actualizar' => ['module' => 'componentes', 'action' => 'editar'],
            '/componentes/eliminar' => ['module' => 'componentes', 'action' => 'eliminar'],

            '/planes-celulares' => ['module' => 'planes_celulares', 'action' => 'ver'],
            '/planes-celulares/guardar' => ['module' => 'planes_celulares', 'action' => 'crear'],
            '/planes-celulares/editar' => ['module' => 'planes_celulares', 'action' => 'editar'],
            '/planes-celulares/actualizar' => ['module' => 'planes_celulares', 'action' => 'editar'],
            '/planes-celulares/eliminar' => ['module' => 'planes_celulares', 'action' => 'eliminar'],

            '/asignaciones' => ['module' => 'asignaciones', 'action' => 'ver'],
            '/asignaciones/guardar' => ['module' => 'asignaciones', 'action' => 'crear'],
            '/asignaciones/ver' => ['module' => 'asignaciones', 'action' => 'ver'],
            '/asignaciones/devolverEquipo' => ['module' => 'asignaciones', 'action' => 'recibir'],
            '/asignaciones/devolverCelular' => ['module' => 'asignaciones', 'action' => 'recibir'],
            '/asignaciones/devolverComponente' => ['module' => 'asignaciones', 'action' => 'recibir'],
            '/asignaciones/eliminar' => ['module' => 'asignaciones', 'action' => 'eliminar'],

            '/colaboradores' => ['module' => 'colaboradores', 'action' => 'ver'],
            '/colaboradores/guardar' => ['module' => 'colaboradores', 'action' => 'crear'],
            '/colaboradores/editar' => ['module' => 'colaboradores', 'action' => 'editar'],
            '/colaboradores/actualizar' => ['module' => 'colaboradores', 'action' => 'editar'],
            '/colaboradores/eliminar' => ['module' => 'colaboradores', 'action' => 'eliminar'],

            '/documentos' => ['module' => 'documentos', 'action' => 'ver'],
            '/documentos/guardar' => ['module' => 'documentos', 'action' => 'crear'],
            '/documentos/actualizar' => ['module' => 'documentos', 'action' => 'editar'],
            '/documentos/eliminar' => ['module' => 'documentos', 'action' => 'eliminar'],

            '/inventario' => ['module' => 'inventario', 'action' => 'ver'],
            '/equipos-asignados' => ['module' => 'equipos_asignados', 'action' => 'ver'],
            '/equipos-baja' => ['module' => 'equipos_baja', 'action' => 'ver'],
            '/mantenimientos' => ['module' => 'mantenimientos', 'action' => 'ver'],
            '/mantenimientos/guardar' => ['module' => 'mantenimientos', 'action' => 'crear'],
            '/mantenimientos/editar' => ['module' => 'mantenimientos', 'action' => 'editar'],
            '/mantenimientos/actualizar' => ['module' => 'mantenimientos', 'action' => 'editar'],
            '/mantenimientos/eliminar' => ['module' => 'mantenimientos', 'action' => 'eliminar'],
            '/estadisticas' => ['module' => 'estadisticas', 'action' => 'ver'],
            '/estadisticas/imprimir' => ['module' => 'estadisticas', 'action' => 'ver'],
            '/auditoria' => ['module' => 'auditoria', 'action' => 'ver'],
            '/usuarios' => ['module' => 'usuarios', 'action' => 'ver'],
            '/usuarios/guardar' => ['module' => 'usuarios', 'action' => 'crear'],
            '/usuarios/eliminar' => ['module' => 'usuarios', 'action' => 'eliminar'],
            '/usuarios/permisos' => ['module' => 'usuarios', 'action' => 'permisos'],
            '/usuarios/permisos/guardar' => ['module' => 'usuarios', 'action' => 'permisos'],
        ];

        if (isset($routePermissions[$route])) {
            $permission = $routePermissions[$route];
            $permisos = $_SESSION['permisos'] ?? [];
            $modulePermisos = $permisos[$permission['module']] ?? [];

            if (empty($modulePermisos[$permission['action']])) {
                header("Location: " . $base_path . "/dashboard");
                exit();
            }
        }
    }
}