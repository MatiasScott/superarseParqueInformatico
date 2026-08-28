<?php

namespace App\Controllers\Inventario;

use App\Models\Inventario\EquiposAsignadosModel;

class EquiposAsignadosController {
    private $model;

    public function __construct() {
        $this->model = new EquiposAsignadosModel();
    }

    /**
     * Renderiza el reporte especializado de asignaciones operativas vigentes
     */
    public function index(): void {
        $equipos = $this->model->obtenerEquiposAsignados();
        $metricas = $this->model->obtenerMetricasAsignados();

        // Alerta al layout maestro para encender la pestaña correcta en la barra lateral
        $activePage = 'equipos-asignados'; 
        
        ob_start();
        // Subimos dos niveles utilizando __DIR__ de forma geométrica segura para el PSR-4
        include __DIR__ . '/../../Views/asignaciones/equipos_asignados.php'; 
        $content = ob_get_clean();

        include __DIR__ . '/../../Views/layouts/main.php';
    }
}