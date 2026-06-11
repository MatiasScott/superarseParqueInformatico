<?php

// 1. Mantenemos tu espacio de nombres físico correcto
namespace App\Controllers\Colaboradores;

use App\Models\Colaboradores\UsuariosModel;

class UsuariosController {
    private $model;

    // Colocamos el modelo en el constructor para limpiar el código de los métodos
    public function __construct() {
        $this->model = new UsuariosModel();
    }

    /**
     * Renderiza el listado maestro de usuarios administradores y técnicos
     */
    public function index() {
        $usuarios = $this->model->getAll();
        $totalUsuarios = $this->model->count();

        // Alerta opcional para pintar la pestaña activa en tu menú lateral
        $activePage = 'usuarios'; 

        ob_start();
        // Sube dos niveles exactos desde Controllers/Colaboradores hacia Views
        require_once __DIR__ . '/../../Views/colaboradores/usuarios.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../../Views/layouts/main.php';
    }

    /**
     * Procesa la inserción o actualización de la entidad de forma unificada
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            // Capturamos el estado de manera segura. Si viene marcado lo volvemos 1, si no, 0.
            // Esto es ideal si manejas un input tipo checkbox o un select en el HTML.
            $estadoInput = $_POST['estado'] ?? '1';
            $estado = ($estadoInput === '1' || $estadoInput === 'on') ? 1 : 0;

            $data = [
                'nombre'   => trim($_POST['nombre'] ?? ''),
                'email'    => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'rol'      => $_POST['rol'] ?? 'usuario',
                'estado'   => $estado // <-- NUEVO: Sincronizado con el modelo blindado
            ];

            if ($id) {
                // Modo Edición
                $id = (int)$id;
                $this->model->update($id, $data);
            } else {
                // Modo Registro Nuevo
                if (!empty($data['nombre']) && !empty($data['email']) && !empty($data['password'])) {
                    $this->model->create($data);
                }
            }

            header('Location: /superarseParqueInformatico/public/usuarios');
            exit;
        }
    }

    /**
     * Ejecuta la desincorporación física del usuario del sistema
     */
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->delete((int)$id);
        }
        header('Location: /superarseParqueInformatico/public/usuarios');
        exit;
    }

    public function permisos() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: /superarseParqueInformatico/public/dashboard');
            exit();
        }

        $usuarios = $this->model->getAll();
        $activePage = 'usuarios_permisos';

        ob_start();
        require_once __DIR__ . '/../../Views/colaboradores/permisos.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../../Views/layouts/main.php';
    }

    public function guardarPermisos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /superarseParqueInformatico/public/usuarios/permisos');
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: /superarseParqueInformatico/public/dashboard');
            exit();
        }

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $permisos = $_POST['permisos'] ?? [];

        if ($userId) {
            $this->model->updatePermissions($userId, $permisos);

            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $userId) {
                $_SESSION['permisos'] = $permisos;
            }
        }

        header('Location: /superarseParqueInformatico/public/usuarios/permisos?success=1');
        exit();
    }
}