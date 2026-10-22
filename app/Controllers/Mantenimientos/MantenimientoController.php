<?php

namespace App\Controllers\Mantenimientos;

use App\Models\Mantenimientos\MantenimientoModel;
use App\Models\Equipos\Equipo;
use App\Helpers\Database;
use PDO;

class MantenimientoController {

    public function index() {
        $model = new MantenimientoModel();
        $mantenimientos = $model->getAll();
        $equipos = (new Equipo())->getAll();

        ob_start();
        require_once __DIR__ . '/../../Views/mantenimientos/index.php';
        $content = ob_get_clean();

        $activePage = 'mantenimientos';

        require_once __DIR__ . '/../../Views/layouts/main.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'equipo_id'           => $_POST['equipo_id'],
                'tipo'                => $_POST['tipo'], 
                'descripcion_falla'   => trim($_POST['descripcion_falla']),
                'tecnico_responsable' => trim($_POST['tecnico_responsable']),
                'fecha_ingreso'       => $_POST['fecha_ingreso'],
                'fecha_salida'        => !empty($_POST['fecha_salida']) ? $_POST['fecha_salida'] : null,
                'estado'              => $_POST['estado'] ?? 'Pendiente',
                'tareas_realizadas'   => !empty($_POST['tareas_realizadas']) ? trim($_POST['tareas_realizadas']) : null,
                'observaciones'       => !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : null
            ];

            $result = (new MantenimientoModel())->create($data);
            
            $msg = $result ? 'guardado' : 'error';
            header("Location: /mantenimientos?msg=" . $msg);
            exit();
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;
        if (!$id) { 
            header("Location: /mantenimientos"); 
            exit(); 
        }

        $mantenimiento = (new MantenimientoModel())->find($id);
        $equipos = (new Equipo())->getAll();

        ob_start();
        require_once __DIR__ . '/../../Views/mantenimientos/editar.php';
        $content = ob_get_clean();

        $activePage = 'mantenimientos';

        require_once __DIR__ . '/../../Views/layouts/main.php';
    }

    /**
     * �9�5 ACTUALIZAR ORDEN Y SINCRONIZAR ESTADO DEL EQUIPO (CONTROL TOTAL PHP)
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $equipo_id = intval($_POST['equipo_id']);
            $nuevo_estado_mantenimiento = $_POST['estado'];
            
            $data = [
                'equipo_id'           => $equipo_id,
                'tipo'                => $_POST['tipo'],
                'descripcion_falla'   => trim($_POST['descripcion_falla']),
                'tecnico_responsable' => trim($_POST['tecnico_responsable']),
                'fecha_ingreso'       => $_POST['fecha_ingreso'],
                'fecha_salida'        => !empty($_POST['fecha_salida']) ? $_POST['fecha_salida'] : null,
                'estado'              => $nuevo_estado_mantenimiento, 
                'tareas_realizadas'   => !empty($_POST['tareas_realizadas']) ? trim($_POST['tareas_realizadas']) : null,
                'observaciones'       => !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : null
            ];
            
            // 1. Actualiza la orden de mantenimiento
            $result = (new MantenimientoModel())->update($id, $data);

            // 2. �0�1�1�5 CONTROL DE ESTADO EN PRODUCCI�0�7N (REEMPLAZA EL TRIGGER EN PHP)
            if ($result && $nuevo_estado_mantenimiento === 'Finalizado') {
                try {
                    $db = Database::getConnection();
                    
                    // Consultamos si el equipo pertenece a un acta vigente y sigue 'En Uso'
                    $sqlCheck = "SELECT COUNT(1) AS tiene_acta 
                                 FROM actas_asignacion_detalle aad
                                 INNER JOIN actas_asignacion aa ON aad.acta_id = aa.id
                                 WHERE aad.equipo_id = :equipo_id 
                                   AND aad.estado_item = 'En Uso' 
                                   AND aa.estado_acta = 'Vigente'";
                    
                    $stmt = $db->prepare($sqlCheck);
                    $stmt->execute([':equipo_id' => $equipo_id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row && $row['tiene_acta'] > 0) {
                        // CASO A: Tiene acta activa -> Buscar ID din��mico de 'Asignado'
                        $stmtEstado = $db->prepare("SELECT id FROM estados_equipo WHERE LOWER(TRIM(nombre)) = 'asignado' LIMIT 1");
                        $stmtEstado->execute();
                        $idEstadoNuevo = $stmtEstado->fetchColumn();
                    } else {
                        // CASO B: NO tiene acta activa -> Buscar ID din��mico de 'Disponible' (Adi��s dependencia del Trigger)
                        $stmtEstado = $db->prepare("SELECT id FROM estados_equipo WHERE LOWER(TRIM(nombre)) = 'disponible' LIMIT 1");
                        $stmtEstado->execute();
                        $idEstadoNuevo = $stmtEstado->fetchColumn();
                    }

                    // Si encontramos el ID correspondiente del estado (sea Asignado o Disponible), actualizamos el equipo
                    if ($idEstadoNuevo !== false) {
                        $stmtUpdate = $db->prepare("UPDATE equipos SET estado_id = :estado_id WHERE id = :equipo_id");
                        $stmtUpdate->execute([
                            ':estado_id' => (int)$idEstadoNuevo,
                            ':equipo_id' => $equipo_id
                        ]);
                    }
                } catch (\Exception $e) {
                    // Evitamos que un fallo de sincronizaci��n rompa la redirecci��n de producci��n, pero puedes capturar el log si lo requieres
                }
            }

            $msg = $result ? 'actualizado' : 'error';
            header("Location: /mantenimientos?msg=" . $msg);
            exit();
        }
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        $result = false;
        
        if ($id) { 
            $result = (new MantenimientoModel())->delete($id); 
        }
        
        $msg = $result ? 'eliminado' : 'error';
        header("Location: /mantenimientos?msg=" . $msg);
        exit();
    }
}