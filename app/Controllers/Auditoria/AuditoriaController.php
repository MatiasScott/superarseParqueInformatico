<?php

namespace App\Controllers\Auditoria;

use App\Models\Auditoria\AuditoriaModel;

class AuditoriaController {
    private $model;

    public function __construct() {
        $this->model = new AuditoriaModel();
    }

    /**
     * Renderiza la bitácora forense de auditoría interna
     */
    public function index(): void {
        $logs = $this->model->obtenerLogsAuditoria();
        $metricas = $this->model->obtenerMetricasAuditoria();

        // Variable de enfoque para el menú de navegación del layout maestro
        $activePage = 'auditoria'; 
        
        ob_start();
        include __DIR__ . '/../../Views/auditoria/index.php'; 
        $content = ob_get_clean();

        include __DIR__ . '/../../Views/layouts/main.php';
    }
}