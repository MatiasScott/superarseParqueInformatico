<?php

namespace App\Controllers\Estadistica;

use App\Models\Estadistica\EstadisticaModel;

class EstadisticasController {
    private $estadisticaModel;

    public function __construct() {
        $this->estadisticaModel = new EstadisticaModel();
    }

    public function index() {
        // 🔍 1. Captura de filtros desde la URL (Mapeo cruzado de pestañas independientes)
        $tipo         = !empty($_GET['tipo']) ? $_GET['tipo'] : null;
        $modelo       = !empty($_GET['modelo']) ? trim($_GET['modelo']) : null;
        $operador     = !empty($_GET['operador']) ? trim($_GET['operador']) : null;
        
        // El formulario de hardware usa 'sede_id', el de celulares usa 'sede_id_cel'
        $sede_id      = !empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
        $sede_id_cel  = !empty($_GET['sede_id_cel']) ? (int)$_GET['sede_id_cel'] : null;

        // 🏢 2. Catálogo de Sedes para renderizar los select de ambos formularios
        $sedesDisponible = $this->estadisticaModel->obtenerSedesDisponibles();

        // 🖥️ 3. Consultas para la sección de HARDWARE (Usan el $sede_id de su propio formulario)
        $resumenEquipos  = $this->estadisticaModel->getEstadisticasEquipos($tipo, $sede_id, $modelo);
        $listadoEquipos  = $this->estadisticaModel->getListadoEquiposReporte($tipo, $sede_id, $modelo) ?? [];
        $graficoHardware = $this->estadisticaModel->getInversionPorTipo($tipo, $sede_id, $modelo);

        // 🛠️ INYECCIÓN DINÁMICA: Adjuntar componentes a cada equipo en la lista de la interfaz web
        foreach ($listadoEquipos as &$equipo) {
            $equipo['componentes'] = $this->estadisticaModel->getComponentesPorEquipo((int)$equipo['id']) ?? [];
        }
        unset($equipo); // Romper la referencia en memoria

        // 📊 CONSULTAS DE COMPONENTES: Métricas y datos de gráfica agrupada (respeta filtros de sede)
        $resumenComponentes = $this->estadisticaModel->getEstadisticasComponentes($sede_id);
        $graficoComponentes = $this->estadisticaModel->getCantidadPorComponente($sede_id);

        // 📱 4. Consultas dinámicas para la sección de TELEFONÍA (Usan el $sede_id_cel de su propio formulario)
        $resumenCelulares = $this->estadisticaModel->getEstadisticasCelulares($sede_id_cel, $operador);
        $listadoCelulares = $this->estadisticaModel->getListadoPlanesReporte($sede_id_cel, $operador);
        $graficoCelulares = $this->estadisticaModel->getGastoPorOperador($sede_id_cel);

        // 📦 Empaquetar todo en un array y extraerlo para forzar el alcance global en las vistas
        $data = [
            'sedesDisponible'    => $sedesDisponible,
            'resumenEquipos'     => $resumenEquipos,
            'listadoEquipos'     => $listadoEquipos,
            'graficoHardware'    => $graficoHardware,
            'resumenComponentes' => $resumenComponentes,
            'graficoComponentes' => $graficoComponentes,
            'resumenCelulares'   => $resumenCelulares,
            'listadoCelulares'   => $listadoCelulares,
            'graficoCelulares'   => $graficoCelulares
        ];
        
        // Esto convierte las llaves del array en variables independientes accesibles en cualquier require posterior
        extract($data); 

        // 📁 5. Renderizado de la vista principal
        ob_start();
        require __DIR__ . '/../../Views/estadisticas/index.php'; 
        $content = ob_get_clean();

        $activePage = 'estadisticas';
        require __DIR__ . '/../../Views/Layouts/main.php';
    }

    public function exportarPdf() {
        // 🔍 1. Detectar el bloque o tipo de reporte solicitado
        $reporte  = $_GET['reporte'] ?? 'hardware';
        $tipo     = !empty($_GET['tipo']) ? $_GET['tipo'] : null;
        $modelo   = !empty($_GET['modelo']) ? trim($_GET['modelo']) : null;
        $operador = !empty($_GET['operador']) ? trim($_GET['operador']) : null;

        // 🏢 2. Mapeo inteligente y unificado de sedes para el motor SQL de los modelos
        $sede_id = ($reporte === 'celulares') 
            ? (!empty($_GET['sede_id_cel']) ? (int)$_GET['sede_id_cel'] : null) 
            : (!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null);

        // 🧪 3. Inicializar variables en arreglos limpios para blindar la vista contra errores de conteo
        $listadoEquipos   = [];
        $listadoCelulares = [];

        // 📊 4. Carga segmentada y bajo demanda de datos según la solicitud del usuario mayor
        if ($reporte === 'celulares') {
            $listadoCelulares = $this->estadisticaModel->getListadoPlanesReporte($sede_id, $operador) ?? [];
        } else {
            $listadoEquipos   = $this->estadisticaModel->getListadoEquiposReporte($tipo, $sede_id, $modelo) ?? [];
            
            // 🛠️ INYECCIÓN DINÁMICA EN PDF: Cargar sub-componentes para el reporte estructurado de hardware
            foreach ($listadoEquipos as &$equipo) {
                $equipo['componentes'] = $this->estadisticaModel->getComponentesPorEquipo((int)$equipo['id']) ?? [];
            }
            unset($equipo);
        }

        // 📁 5. Inclusión del formato de impresión limpio y especializado
        require_once __DIR__ . '/../../Views/estadisticas/pdf_formato.php';
    }
}