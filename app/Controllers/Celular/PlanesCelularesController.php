<?php

namespace App\Controllers\Celular;

use App\Models\Celular\PlanesCelularesModel;
use Exception;

class PlanesCelularesController {
    
    private $planesModel;

    public function __construct() {
        $this->planesModel = new PlanesCelularesModel();
    }

   /**
     * Muestra la tabla principal del inventario de telefonía con soporte de búsqueda
     */
    public function index() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $items = $this->planesModel->getAll($search);
        $activePage = 'planes-celulares'; 
        
        ob_start();
        require_once __DIR__ . '/../../Views/celular/planes_celulares.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../Views/layouts/main.php';
    }

    /**
     * Procesa la inserción del formulario POST
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // 🛠️ Saneamos los datos antes de enviarlos al modelo
                $data = $this->sanearDatosCelular($_POST);

                $this->planesModel->create($data);
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
                // 🛠️ Saneamos los datos antes de enviarlos al modelo
                $data = $this->sanearDatosCelular($_POST);

                $this->planesModel->update($id, $data);
                $_SESSION['flash_success'] = "Los cambios del dispositivo fueron guardados con éxito.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Error al actualizar el registro: " . $e->getMessage();
            }
            
            header('Location: /planes-celulares');
            exit;
        }
    }

    /**
     * 💡 MÉTODO AUXILIAR: Convierte valores vacíos o 'N/D' en NULL reales
     * para evitar errores de duplicación en la base de datos.
     */
    private function sanearDatosCelular(array $datosOriginales) {
        $datosSaneados = $datosOriginales;

        $nombrePlan = trim((string)($datosSaneados['nombre_plan'] ?? ''));
        if ($nombrePlan === '') {
            $datosSaneados['nombre_plan'] = 'Sin plan';
            $datosSaneados['costo_mensual'] = 0;
        } else {
            $datosSaneados['nombre_plan'] = $nombrePlan;
        }

        // Lista de los campos críticos del hardware del teléfono
        $camposCriticos = ['celular_serie', 'celular_imei_1', 'celular_imei_2', 'celular_marca', 'celular_modelo', 'celular_color'];

        foreach ($camposCriticos as $campo) {
            if (isset($datosSaneados[$campo])) {
                $valor = trim($datosSaneados[$campo]);
                // Si el campo está vacío, contiene solo espacios, o dice explicitamente 'N/D' (mayúscula o minúscula)
                if ($valor === '' || strtoupper($valor) === 'N/D') {
                    $datosSaneados[$campo] = null; // Lo transformamos en NULL real
                } else {
                    $datosSaneados[$campo] = $valor;
                }
            }
        }

        return $datosSaneados;
    }
}