<?php

namespace App\Controllers\Equipos;

use App\Models\Equipos\HistorialModel;

class HistorialController {

    // LISTAR EL HISTORIAL GENERAL
    public function index()
    {
        $model = new HistorialModel();

        // Obtenemos todos los movimientos ordenados cronológicamente
        $historial = $model->getHistorialCompleto();

        // Iniciamos el almacenamiento en búfer para renderizar la vista
        ob_start();

        // Cargamos la vista correspondiente al historial
        require_once __DIR__ . '/../../Views/equipos/historial.php';

        $content = ob_get_clean();

        // Inyectamos el contenido en la plantilla principal de la app
        require_once __DIR__ . '/../../Views/layouts/main.php';
    }
}