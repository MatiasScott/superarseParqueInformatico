<?php

namespace App\Controllers\Componentes;

use App\Models\Componentes\ComponenteModel;
use App\Models\Equipos\Equipo;

class ComponenteController {

   // LISTAR
    public function index() {
        $model = new ComponenteModel();
        $equipoModel = new Equipo(); 

        // Capturamos el ID del equipo si viene por la URL
        $equipo_seleccionado = !empty($_GET['equipo_id']) ? intval($_GET['equipo_id']) : null;

        // Pasamos el filtro al Modelo (este ya filtra internamente para no traer componentes 'Eliminados')
        $componentes = $model->getAll($equipo_seleccionado);
        $totalComponentes = $model->count($equipo_seleccionado);
        
        // 🛡️ Trae los equipos para el select de filtros cotidianos
        $equipos = $equipoModel->getAll(); 
        $activePage = 'componentes';

        ob_start();
        require_once ROOT . 'Views/componentes/index.php'; 
        $content = ob_get_clean();
        require_once ROOT . 'Views/layouts/main.php';
    }

    // GUARDAR EN LOTE
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $componentesEnviados = $_POST['componentes'] ?? [];

            if (!empty($componentesEnviados) && is_array($componentesEnviados)) {
                $model = new ComponenteModel();

                foreach ($componentesEnviados as $item) {
                    // Validar campo requerido de tipo de componente enum
                    if (empty($item['tipo']) || empty($item['equipo_id'])) {
                        continue; // No se puede guardar sin tipo o sin equipo asociado por restricciones FK
                    }

                    $data = [
                        'equipo_id'   => $item['equipo_id'],
                        'tipo'        => $item['tipo'], // RAM, Disco Duro, etc.
                        'marca_modelo'=> trim($item['marca_modelo'] ?? 'Genérico'),
                        'descripcion' => trim($item['descripcion']), // Capacidad/Detalle
                        'estado'      => $item['estado'] ?? 'Bueno'
                    ];

                    $model->create($data);
                }
            }

            header("Location: /superarseParqueInformatico/public/componentes?msg=guardado");
            exit();
        }
    }

    // ACTUALIZAR
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];

            if (empty($_POST['equipo_id'])) {
                header("Location: /superarseParqueInformatico/public/componentes?msg=error");
                exit();
            }

            $data = [
                'equipo_id'   => $_POST['equipo_id'],
                'tipo'        => $_POST['tipo'],
                'marca_modelo'=> trim($_POST['marca_modelo'] ?? 'Genérico'),
                'descripcion' => trim($_POST['descripcion']),
                'estado'      => $_POST['estado']
            ];

            (new ComponenteModel())->update($id, $data);

            header("Location: /superarseParqueInformatico/public/componentes?msg=actualizado");
            exit();
        }
    }

    // FORM CREAR
    public function crear() {
        $equipos = (new Equipo())->getAll();
        $activePage = 'componentes';
        ob_start();
        require_once ROOT . 'Views/componentes/crear.php';
        $content = ob_get_clean();
        require_once ROOT . 'Views/layouts/main.php';
    }

    // FORM EDITAR
    public function editar() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: /superarseParqueInformatico/public/componentes"); exit(); }

        $componente = (new ComponenteModel())->find($id);
        $equipos = (new Equipo())->getAll();
        $activePage = 'componentes';

        ob_start();
        require_once ROOT . 'Views/componentes/editar.php';
        $content = ob_get_clean();
        require_once ROOT . 'Views/layouts/main.php';
    }

    // ELIMINAR (BORRADO LÓGICO PASIVO)
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        
        if ($id) { 
            // 🛡️ El modelo cambiará dinámicamente el estado a 'Eliminado' sin romper registros históricos
            (new ComponenteModel())->delete($id); 
        }
        
        header("Location: /superarseParqueInformatico/public/componentes?msg=eliminado");
        exit();
    }
}