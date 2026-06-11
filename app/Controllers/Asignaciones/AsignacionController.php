<?php

namespace App\Controllers;

use App\Models\AsignacionModel;

class AsignacionController {
    
    private $asignacionModel;

    public function __construct() {
        $this->asignacionModel = new AsignacionModel();
    }

    /**
     * Acción encargada de procesar el formato de impresión
     */
    public function imprimirActa() {
        // Captura del ID por GET
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: /superarseParqueInformatico/public/asignaciones?msg=error');
            exit;
        }

        // Obtener la cabecera mapeada con la nueva base de datos
        $acta = $this->asignacionModel->obtenerActaPorId($id);

        if (empty($acta)) {
            echo "El acta de asignación no existe en la base de datos.";
            exit;
        }

        // Incrustar los equipos usando la tabla relacional correcta
        $acta['equipos'] = $this->asignacionModel->obtenerEquiposPorActaId($id);

        // 📱 NUEVO: Incrustar los celulares vinculados al acta usando el nuevo método del modelo
        $acta['celulares'] = $this->asignacionModel->obtenerCelularesPorActaId($id);

        // Renderizar la vista pasando la variable $acta
        // '__DIR__ . /../' sube un nivel saliendo de la carpeta 'Controllers' y entra a 'Views'
         require_once __DIR__ . '/../../Views/asignaciones/imprimir_formato.php';
    }
}