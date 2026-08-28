<?php

namespace App\Controllers\Equipos;

use App\Models\Equipos\BajasModel;

class BajasController {

    public function index() {
        $model = new BajasModel();
        $equiposBaja = $model->getEquiposDeBaja();

        ob_start();
        // Ajustamos la ruta para que busque en la carpeta 'bajas' el archivo 'index.php'
        require_once __DIR__ . '/../../Views/bajas/index.php';
        $content = ob_get_clean();

        // 💡 CORRECCIÓN INTEGRAL: Coincide exactamente con la línea 19 de tu main.php
        $activePage = 'equipos-baja'; 

        require_once __DIR__ . '/../../Views/layouts/main.php';
    }
}