<?php

namespace App\Controllers\Dashboard;

use App\Models\Dashboard\Dashboard;

class DashboardController
{
    public function index()
    {
        $dashboard = new Dashboard();

        // 📊 1. Obtener datos básicos de los contadores (Métricas KPI)
        $totalEquipos = $dashboard->totalEquipos();
        $equiposAsignados = $dashboard->equiposAsignados();
        $totalColaboradores = $dashboard->totalColaboradores();
        $equiposMantenimiento = $dashboard->equiposMantenimiento();

        // 🛠️ 2. Obtener datos analíticos de componentes en stock
        $compInfo = $dashboard->infoComponentes();
        $totalComponentes = $compInfo['total'] ?? 0;
        $componentesBuenos = $compInfo['buenos'] ?? 0;
        $componentesRegulares = $compInfo['regulares'] ?? 0;
        $componentesDanados = $compInfo['danados'] ?? 0;

        // 📋 3. Obtener listados para las tablas en tiempo real (Límite de 3 registros)
        $ultimasAsignaciones = $dashboard->ultimasAsignaciones(3);
        $mantenimientosCriticos = $dashboard->mantenimientosCriticos(3);

        // Iniciar captura de contenido
        ob_start();

        // --- OPCIÓN DE RUTA COMÚN ---
        // Si tu archivo está en app/Views/dashboard/index.php, usa esta:
        if (file_exists(ROOT . 'Views/dashboard/index.php')) {
            require_once ROOT . 'Views/dashboard/index.php';
        } 
        // Si tu archivo está directamente en app/Views/dashboard.php, usa esta:
        else {
            require_once ROOT . 'Views/dashboard.php';
        }

        $content = ob_get_clean();

        // Cargar el diseño principal
        require_once ROOT . 'Views/Layouts/main.php';
    }
}