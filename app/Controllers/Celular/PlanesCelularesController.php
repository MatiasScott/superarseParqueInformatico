<?php

namespace App\Controllers\Celular;

use App\Models\Celular\PlanesCelularesModel;
use Exception;

class PlanesCelularesController {
    
    private $planesModel;

    public function __construct() {
        // Tu autoloader ahora encontrará la clase perfectamente
        $this->planesModel = new PlanesCelularesModel();
    }

   /**
     * Muestra la tabla principal del inventario de telefonía con soporte de búsqueda
     */
    public function index() {
        // 1. Capturamos el texto de búsqueda si el usuario escribió algo
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // 2. Pasamos el término al modelo (usando la función modificada en el paso anterior)
        $items = $this->planesModel->getAll($search);
        
        $activePage = 'planes-celulares'; 
        
        // Renderizado usando almacenamiento en búfer para inyectar en el layout maestro
        ob_start();
        // Nota: La variable $search ahora está disponible dentro de tu archivo de vista automáticamente
        require_once __DIR__ . '/../../Views/celular/planes_celulares.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../Views/Layouts/main.php';
    }

    /**
     * Procesa la inserción del formulario POST
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->planesModel->create($_POST);
                $_SESSION['flash_success'] = "Dispositivo y Plan celular registrados correctamente.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Error al guardar el registro: " . $e->getMessage();
            }
            header('Location: /planes-celulares');
            exit;
        }
    }

    /**
     * Procesa la eliminación mediante el ID recibido por parámetro GET
     */
    public function destroy() {
        // Captura del ID tal como lo haces en tu ejemplo
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID de dispositivo no válido.";
            header('Location: /planes-celulares');
            exit;
        }

        try {
            $this->planesModel->delete($id);
            $_SESSION['flash_success'] = "Registro eliminado del inventario global.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "No se puede remover: El plan tiene histórico asignado en actas.";
        }
        
        header('Location: /planes-celulares');
        exit;
    }

    /**
     * Retorna los datos de un dispositivo en formato JSON para la edición dinámica (Modal)
     */
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $celular = $this->planesModel->getById($id);

        header('Content-Type: application/json');
        if ($celular) {
            echo json_encode(['status' => 'success', 'data' => $celular]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dispositivo no encontrado.']);
        }
        exit;
    }

    /**
     * Procesa la actualización del formulario POST
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

            if ($id <= 0) {
                $_SESSION['flash_error'] = "ID de dispositivo no válido para actualizar.";
                header('Location: /planes-celulares');
                exit;
            }

            try {
                $this->planesModel->update($id, $_POST);
                $_SESSION['flash_success'] = "Los cambios del dispositivo fueron guardados con éxito.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Error al actualizar el registro: " . $e->getMessage();
            }
            
            header('Location: /planes-celulares');
            exit;
        }
    }
}