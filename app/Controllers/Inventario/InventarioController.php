<?php

namespace App\Controllers\Inventario;

use App\Models\Inventario\InventarioModel;

class InventarioController {
    private $inventarioModel;

    public function __construct() {
        $this->inventarioModel = new InventarioModel();
    }

    /**
     * Renderiza la vista del Inventario General (Equipos + Celulares) dentro del Layout Maestro
     */
    public function index(): void {
        // 1. Consumo de datos consolidados (Unificación de Equipos y Celulares)
        $equipos = $this->inventarioModel->obtenerInventarioGeneral() ?? [];
        $metricas = $this->inventarioModel->obtenerMetricasReporte() ?? [
            'total_equipos' => 0, 'disponibles' => 0, 
            'asignados' => 0, 'mantenimiento' => 0, 'bajas_danos' => 0
        ];

        // 2. Variable requerida por el layout centralizado para el estado activo de la navegación
        $activePage = 'inventario'; 
        
        // 3. Captura de flujo mediante Buffer de salida para inyección limpia
        ob_start();
        
        // 🚀 UBICACIÓN: Subimos dos niveles para salir de Controllers/Inventario/ y entrar a Views/
        include __DIR__ . '/../../Views/inventario/index.php'; 
        $content = ob_get_clean();

        // 🚀 UBICACIÓN: Subimos dos niveles aquí también para renderizar el contenedor maestro
        include __DIR__ . '/../../Views/Layouts/main.php';
    }
}