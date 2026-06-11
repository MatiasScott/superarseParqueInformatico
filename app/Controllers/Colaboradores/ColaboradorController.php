<?php

namespace App\Controllers\Colaboradores;

use App\Models\Colaboradores\ColaboradorModel;

class ColaboradorController {

    public function index() {
        $model = new ColaboradorModel();
        
        // 1. Cargamos los colaboradores
        $colaboradores = $model->getAll();

        // 2. Cargamos las sedes directamente usando el mismo modelo 🎯
        $sedes = $model->getSedes();

        ob_start();
        require_once __DIR__ . '/../../Views/colaboradores/index.php';
        $content = ob_get_clean();

        // 💡 CLAVE: Avisamos al layout que pinte de azul el menú de colaboradores
        $activePage = 'colaboradores'; 
        
        require_once __DIR__ . '/../../Views/layouts/main.php';
    }
    
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombres'       => trim($_POST['nombres']),
                'cargo'         => !empty($_POST['cargo']) ? trim($_POST['cargo']) : null,
                'correo'        => !empty($_POST['correo']) ? trim($_POST['correo']) : null,
                'area'          => !empty($_POST['area']) ? trim($_POST['area']) : null,
                'fecha_ingreso' => !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null,
                'estado'        => isset($_POST['estado']) ? (int)$_POST['estado'] : 1,
                'sede_id'       => !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : 1 // 🛡️ Corregido: Evita que falle si no se selecciona sede
            ];

            $result = (new ColaboradorModel())->create($data);
            $msg = $result ? 'guardado' : 'error';
            header("Location: /superarseParqueInformatico/public/colaboradores?msg=" . $msg);
            exit();
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /superarseParqueInformatico/public/colaboradores");
            exit();
        }

        // 1. Instanciamos el modelo de colaboradores
        $model = new ColaboradorModel();

        // 2. Buscamos los datos del colaborador a editar
        $colaborador = $model->find($id);
        
        // 3. Cargamos las sedes usando el método interno del mismo modelo 🎯
        $sedes = $model->getSedes() ?? []; 

        ob_start();
        require_once __DIR__ . '/../../Views/colaboradores/editar.php';
        $content = ob_get_clean();

        // 💡 CLAVE: Incluso al editar, el menú lateral debe mantenerse azul en colaboradores
        $activePage = 'colaboradores';

        require_once __DIR__ . '/../../Views/layouts/main.php';
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                header("Location: /superarseParqueInformatico/public/colaboradores?msg=error");
                exit();
            }

            $model = new ColaboradorModel();
            
            // 1. Validar si 'sede_id' fue enviado, de lo contrario rescatar la sede que ya tiene en BD
            if (!empty($_POST['sede_id'])) {
                $sedeId = (int)$_POST['sede_id'];
            } else {
                $colaboradorActual = $model->find($id);
                $sedeId = $colaboradorActual ? (int)$colaboradorActual['sede_id'] : 1; 
            }

            // 2. Mapeo seguro de la información sin índices sueltos
            $data = [
                'nombres'       => trim($_POST['nombres']),
                'cargo'         => !empty($_POST['cargo']) ? trim($_POST['cargo']) : null,
                'correo'        => !empty($_POST['correo']) ? trim($_POST['correo']) : null,
                'area'          => !empty($_POST['area']) ? trim($_POST['area']) : null,
                'fecha_ingreso' => !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null,
                'estado'        => isset($_POST['estado']) ? (int)$_POST['estado'] : 1,
                'sede_id'       => $sedeId
            ];

            // 3. Ejecución de la sentencia en el modelo
            $result = $model->update($id, $data);
            
            $msg = $result ? 'actualizado' : 'error';
            header("Location: /superarseParqueInformatico/public/colaboradores?msg=" . $msg);
            exit();
        }
    }

    public function eliminar() {
        // 🎯 Ajuste de control: Rescata el ID sin importar si viene por GET o por POST
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        $result = false;
        
        if ($id) {
            $result = (new ColaboradorModel())->delete($id);
        }

        // Si retorna false, es por la restricción de llave foránea en la base de datos
        $msg = $result ? 'eliminado' : 'error_fk';
        header("Location: /superarseParqueInformatico/public/colaboradores?msg=" . $msg);
        exit();
    }
}