<?php

namespace App\Controllers\Componentes;

use App\Models\Componentes\ComponenteModel;
use App\Models\Equipos\Equipo;

class ComponenteController {

    /**
     * 📋 LISTAR COMPONENTES
     */
    public function index() {
        $model = new ComponenteModel();
        $equipoModel = new Equipo(); 

        // Capturamos el ID del equipo si viene por la URL
        $equipo_seleccionado = !empty($_GET['equipo_id']) ? intval($_GET['equipo_id']) : null;

        // Pasamos el filtro al Modelo (este ya filtra internamente para no traer componentes 'Eliminados')
        $componentes = $model->getAll($equipo_seleccionado);
        $totalComponentes = $model->count($equipo_seleccionado);
        $componentesBaja = $model->countBaja($equipo_seleccionado);
        
        // 🛡️ Trae los equipos para el select de filtros cotidianos
        $equipos = $equipoModel->getAll(); 
        $activePage = 'componentes';

        ob_start();
        require_once ROOT . 'Views/componentes/index.php'; 
        $content = ob_get_clean();
        require_once ROOT . 'Views/layouts/main.php';
    }

    /**
     * 📥 GUARDAR EN LOTE
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $componentesEnviados = $_POST['componentes'] ?? [];

            if (!empty($componentesEnviados) && is_array($componentesEnviados)) {
                $model = new ComponenteModel();

                foreach ($componentesEnviados as $item) {
                    // El equipo es opcional: un componente puede registrarse directamente en bodega.
                    if (empty($item['tipo'])) {
                        continue; 
                    }

                    $data = [
                        'equipo_id'    => !empty($item['equipo_id']) ? intval($item['equipo_id']) : null,
                        'tipo'         => trim($item['tipo']), 
                        'marca_modelo' => !empty($item['marca_modelo']) ? trim($item['marca_modelo']) : 'Genérico',
                        'descripcion'  => isset($item['descripcion']) ? trim($item['descripcion']) : '', 
                        'estado'       => $item['estado'] ?? 'Bueno'
                    ];

                    $model->create($data);
                }
            }

            header("Location: /componentes?msg=guardado");
            exit();
        }
    }

    /**
     * 📝 ACTUALIZAR COMPONENTE
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;

            if (!$id) {
                header("Location: /componentes");
                exit();
            }

            // Nota de Producción: Si tu base de datos requiere obligatoriamente una FK,
            // asegúrate de que tu vista no envíe un '0' o vacío si el INNER JOIN oculta los NULL.
            $equipo_id = !empty($_POST['equipo_id']) ? intval($_POST['equipo_id']) : null;
            
            $tipo         = !empty($_POST['tipo']) ? trim($_POST['tipo']) : 'Otro';
            $marca_modelo = !empty($_POST['marca_modelo']) ? trim($_POST['marca_modelo']) : 'Genérico';
            $descripcion  = !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : 'Sin especificaciones';
            $estado       = !empty($_POST['estado']) ? trim($_POST['estado']) : 'Bueno';

            // Estructura idéntica compatible con tu ComponenteModel de producción
            $data = [
                'equipo_id'    => $equipo_id,
                'tipo'         => $tipo,         
                'marca_modelo' => $marca_modelo,
                'descripcion'  => $descripcion,   
                'estado'       => $estado
            ];

            try {
                (new ComponenteModel())->update($id, $data);
                header("Location: /componentes?msg=actualizado");
                exit();
            } catch (\Exception $e) {
                header("Location: /componentes?msg=error&debug=" . urlencode($e->getMessage()));
                exit();
            }
        }
    }

    /**
     * Devuelve un componente asignado al stock disponible.
     */
    public function devolver()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /componentes?msg=error_devolver");
            exit();
        }

        $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
        $devuelto = $id ? (new ComponenteModel())->returnToStock($id) : false;

        header("Location: /componentes?msg=" . ($devuelto ? 'devuelto' : 'error_devolver'));
        exit();
    }
    
    /**
     * ➕ FORMULARIO CREAR
     */
    public function crear() {
        $equipos = (new Equipo())->getAll();
        $activePage = 'componentes';
        ob_start();
        require_once ROOT . 'Views/componentes/crear.php';
        $content = ob_get_clean();
        require_once ROOT . 'Views/layouts/main.php';
    }

    /**
     * 🔍 FORMULARIO EDITAR
     */
    public function editar() {
        $id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) { 
            header("Location: /componentes"); 
            exit(); 
        }

        $componente = (new ComponenteModel())->find($id);
        
        // Evitamos que intente renderizar la vista si el componente no existe o está eliminado
        if (!$componente) {
            header("Location: /componentes?msg=no_encontrado");
            exit();
        }

        $equipos = (new Equipo())->getAll();
        $activePage = 'componentes';

        ob_start();
        require_once ROOT . 'Views/componentes/editar.php';
        $content = ob_get_clean();
        require_once ROOT . 'Views/layouts/main.php';
    }

    /**
     * 🗑️ ELIMINAR (BORRADO LÓGICO PASIVO)
     */
    public function eliminar() {
        $id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) { 
            (new ComponenteModel())->delete($id); 
            header("Location: /componentes?msg=eliminado");
        } else {
            header("Location: /componentes?msg=error_eliminar");
        }
        exit();
    }
}