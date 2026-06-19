<?php

namespace App\Controllers\Mantenimientos;

use App\Models\Mantenimientos\MantenimientoModel;
use App\Models\Equipos\Equipo;
use App\Helpers\Database; // Aseguramos la conexión para la verificación rápida
use PDO;

class MantenimientoController {

    public function index() {
        $model = new MantenimientoModel();
        $mantenimientos = $model->getAll();
        $equipos = (new Equipo())->getAll();

        ob_start();
        require_once __DIR__ . '/../../Views/mantenimientos/index.php';
        $content = ob_get_clean();

        // 💡 CLAVE: Indica al layout que pinte de azul la sección "Órdenes de Soporte"
        $activePage = 'mantenimientos';

        require_once __DIR__ . '/../../Views/Layouts/main.php';
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

        // 💡 CLAVE: Incluso al editar una orden, el menú de mantenimientos debe seguir azul
        $activePage = 'mantenimientos';

        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $equipo_id = $_POST['equipo_id'];
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

            // 2. Si la orden fue finalizada con éxito, corregimos el error del Trigger
            if ($result && $nuevo_estado_mantenimiento === 'Finalizado') {
                $db = Database::getConnection();
                
                // Consultamos si el equipo pertenece a un acta vigente y sigue 'En Uso'
                $sqlCheck = "SELECT COUNT(1) AS tiene_acta 
                             FROM actas_asignacion_detalle aad
                             INNER JOIN actas_asignacion aa ON aad.acta_id = aa.id
                             WHERE aad.equipo_id = :equipo_id 
                               AND aad.estado_item = 'En Uso' 
                               AND aad.estado_acta = 'Vigente'";
                
                $stmt = $db->prepare($sqlCheck);
                $stmt->execute([':equipo_id' => $equipo_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row && $row['tiene_acta'] > 0) {
                    // Si tiene acta activa, vuelve a quedar como Asignado resolviendo el estado por nombre.
                    $stmtEstadoAsignado = $db->prepare("SELECT id FROM estados_equipo WHERE LOWER(nombre) = 'asignado' LIMIT 1");
                    $stmtEstadoAsignado->execute();
                    $idEstadoAsignado = $stmtEstadoAsignado->fetchColumn();

                    if ($idEstadoAsignado !== false) {
                        $sqlUpdate = "UPDATE equipos SET estado_id = :estado_id WHERE id = :equipo_id";
                        $stmtUpdate = $db->prepare($sqlUpdate);
                        $stmtUpdate->execute([
                            ':estado_id' => (int)$idEstadoAsignado,
                            ':equipo_id' => $equipo_id
                        ]);
                    }
                }
                // Si no tiene acta, el trigger de la base de datos ya lo dejó en estado 1 (Disponible), lo cual es correcto.
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