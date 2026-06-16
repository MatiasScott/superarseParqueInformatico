<?php

namespace App\Controllers;

// ⬇️ Importaciones de Modelos base y telefonía móvil
use App\Models\Asignaciones\AsignacionesModel;
use App\Models\Colaboradores\ColaboradorModel; 
use App\Models\Equipos\Equipo;                
use App\Models\Celular\PlanesCelularesModel;

class AsignacionesController {

    /**
     * 📋 PANEL PRINCIPAL DE ASIGNACIONES (ACTAS)
     */
    public function index() {
        $model = new AsignacionesModel();
        
        // 🔍 1. Capturamos el filtro si viene por la URL (?colaborador_id=X)
        $colaboradorSeleccionado = $_GET['colaborador_id'] ?? null;

        // 📋 2. Obtenemos las actas pasándole el ID del colaborador (La subconsulta del Modelo ya cuenta los componentes internos)
        $actas = $model->getAll($colaboradorSeleccionado);
        
        // Cargamos todos los colaboradores (Servirá para el filtro principal y para el modal de creación)
        $colaboradores = (new ColaboradorModel())->getAll(); 
        
        // Cargamos solo equipos disponibles sin hardcodear IDs de estado
        $equipos = (new Equipo())->getDisponibles(); 

        // 📱 Cargamos únicamente los terminales/planes celulares que están en estado 'Disponible'
        $celularesDisponibles = $model->getCelularesDisponibles();

        ob_start();
        require_once __DIR__ . '/../../Views/asignaciones/index.php';
        $content = ob_get_clean();

        // 💡 CLAVE: Indica al layout que pinte de azul la sección "Actas de Asignación"
        $activePage = 'asignaciones';

        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    /**
     * ➕ GUARDAR NUEVA ASIGNACIÓN EN LOTE (MAESTRO - DETALLE MULTI-ACTIVO)
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $model = new AsignacionesModel();

            // 1. Recolección de Equipos de Hardware Estándar
            $equiposSeleccionados = $_POST['equipos_seleccionados'] ?? [];
            $equiposDetalle = [];
            
            foreach ($equiposSeleccionados as $equipo_id) {
                $equiposDetalle[] = [
                    'equipo_id'             => $equipo_id,
                    'estado_entrega_equipo' => $_POST['estado_entrega_' . $equipo_id] ?? 'Bueno',
                    'observacion_item'      => !empty($_POST['observacion_' . $equipo_id]) ? trim($_POST['observacion_' . $equipo_id]) : null
                ];
                // 💡 NOTA ARQUITECTÓNICA: Los componentes vinculados a este equipo_id en la tabla 
                // `componentes_equipo` se transfieren implícitamente al colaborador al estar asignado su equipo contenedor.
            }

            // 2. 📱 Recolección estructurada para actas_planes_detalle
            $celularesSeleccionados = $_POST['celulares_seleccionados'] ?? [];
            $celularesDetalle = [];

            foreach ($celularesSeleccionados as $plan_id) {
                $celularesDetalle[] = [
                    'plan_celular_id'     => $plan_id,
                    'estado_entrega'      => $_POST['estado_entrega_cel_' . $plan_id] ?? 'Bueno',
                    'observacion_entrega' => !empty($_POST['observacion_cel_' . $plan_id]) ? trim($_POST['observacion_cel_' . $plan_id]) : null
                ];
            }

            // 3. Validación de consistencia: El acta no puede generarse vacía
            if (empty($equiposDetalle) && empty($celularesDetalle)) {
                header("Location: /asignaciones?msg=sin_activos");
                exit();
            }

            // 🛠️ Control de Sesión Preventivo
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // 4. Estructuramos el payload completo adaptado al AsignacionesModel transaccional
            $data = [
                'codigo_acta'         => $model->generarCodigoActa(), 
                'colaborador_id'      => $_POST['colaborador_id'],
                'usuario_id'          => $_SESSION['usuario_id'] ?? 3, // ID por defecto de respaldo
                'fecha_entrega'       => $_POST['fecha_entrega'],
                'observacion_general' => !empty($_POST['observacion_general']) ? trim($_POST['observacion_general']) : null,
                'equipos'             => $equiposDetalle,
                'celulares'           => $celularesDetalle 
            ];

            // 5. Ejecutamos la inserción integral
            $result = $model->create($data);
            
            $msg = $result ? 'guardado' : 'error';
            header("Location: /asignaciones?msg=" . $msg);
            exit();
        }
    }

    /**
     * 🔍 VISUALIZAR EL DETALLE DE UN ACTA ESPECÍFICA (VISTA DE CONTROL DE ITEMS)
     */
    public function ver() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /asignaciones");
            exit();
        }

        $model = new AsignacionesModel();
        
        // 💡 CLAVE: El método find($id) ya busca y anida internamente los componentes de cada equipo
        $acta = $model->find($id); 

        if (!$acta) {
            header("Location: /asignaciones?msg=no_encontrado");
            exit();
        }

        ob_start();
        require_once __DIR__ . '/../../Views/asignaciones/ver.php';
        $content = ob_get_clean();

        // 💡 CLAVE: Incluso al estar inspeccionando un acta, el menú de asignaciones debe seguir azul
        $activePage = 'asignaciones';

        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    /**
     * ↩️ REGISTRAR LA DEVOLUCIÓN INDIVIDUAL DE UN ACTIVO DEL ACTA (EQUIPO O CELULAR)
     */
    public function devolverEquipo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detalle_id = $_POST['detalle_id'];
            $acta_id = $_POST['acta_id'];
            $observacion_devolucion = !empty($_POST['observacion_devolucion']) ? trim($_POST['observacion_devolucion']) : 'Devolución estándar';

            $model = new AsignacionesModel();
            $result = $model->updateItemDevolucion($detalle_id, $observacion_devolucion);

            $msg = $result ? 'devuelto' : 'error';
            header("Location: /asignaciones/ver?id=" . $acta_id . "&msg=" . $msg);
            exit();
        }
    }

    /**
     * 🗑️ ANULAR / ELIMINAR UN ACTA COMPLETA
     */
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        $result = false;
        
        if ($id) {
            $model = new AsignacionesModel();
            $result = $model->delete($id);
        }
        
        $msg = $result ? 'eliminado' : 'error';
        header("Location: /asignaciones?msg=" . $msg);
        exit();
    }
}