<?php

namespace App\Models\Asignaciones;

use App\Helpers\Database;
use PDO;
use Exception;
use App\Models\Auditoria\AuditoriaModel;

class AsignacionesModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * 📋 LISTAR TODAS LAS ASIGNACIONES (CON CONTADORES REALES)
     */
    public function getAll($colaboradorId = null) {
        // SQL ajustado para contar los componentes de los equipos vinculados a la asignación
        $sql = "SELECT a.id,
                       a.codigo_acta,
                       a.fecha_entrega,
                       a.estado_acta,
                       a.observacion_general,
                       a.fecha_devolucion_global,
                       c.id AS colaborador_id,
                       c.nombres AS colaborador_nombre, 
                       c.cargo AS colaborador_cargo,
                       c.area AS colaborador_area,
                       u.nombre AS usuario_tecnico,
                       (SELECT COUNT(*) FROM actas_asignacion_detalle WHERE acta_id = a.id) as total_equipos,
                       (SELECT COUNT(*) FROM actas_planes_detalle WHERE acta_id = a.id) as total_celulares,
                       (SELECT COUNT(*) 
                        FROM componentes_equipo ce 
                        INNER JOIN actas_asignacion_detalle ad ON ce.equipo_id = ad.equipo_id 
                        WHERE ad.acta_id = a.id) as total_componentes
                FROM actas_asignacion a
                INNER JOIN colaboradores c ON a.colaborador_id = c.id
                INNER JOIN usuarios u ON a.usuario_id = u.id";
        
        if ($colaboradorId) {
            $sql .= " WHERE a.colaborador_id = :colaborador_id";
        }

        $sql .= " GROUP BY a.id ORDER BY a.fecha_entrega DESC, a.id DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($colaboradorId) {
            $stmt->execute([':colaborador_id' => $colaboradorId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🔍 BUSCAR UNA ASIGNACIÓN POR ID CON SUS DETALLES COMPLETOS
     */
    public function find($id) {
        $sql = "SELECT a.*, 
                       c.nombres AS colaborador_nombre, c.correo AS colaborador_correo, 
                       c.cargo AS colaborador_cargo, c.area AS colaborador_area,
                       u.nombre AS usuario_tecnico
                FROM actas_asignacion a
                INNER JOIN colaboradores c ON a.colaborador_id = c.id
                INNER JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $acta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($acta) {
            // 1. Traemos los equipos
            $acta['equipos'] = $this->getDetallesActa($id);
            
            // 2. Traemos los celulares
            $acta['celulares'] = $this->obtenerCelularesPorActaId($id);
            
            // ⭐ 3. Traemos todos los componentes de forma global para la nueva tabla
            $acta['componentes'] = $this->obtenerComponentesPorActaId($id);
            
            // 4. Inyectamos los componentes internos dentro de cada equipo por si se requiere compatibilidad estructural
            if (!empty($acta['equipos'])) {
                foreach ($acta['equipos'] as &$equipo) {
                    $equipo['componentes_internos'] = $this->getComponentesPorEquipo($equipo['equipo_id']);
                }
            }
        }

        return $acta;
    }

    /**
     * 🔍 OBTENER LOS EQUIPOS COMPONENTES DE UN ACTA (DETALLE EQUIPOS)
     */
    public function getDetallesActa($acta_id) {
        $sql = "SELECT d.*, e.tipo, e.nombre, e.marca, e.modelo, e.serie
                FROM actas_asignacion_detalle d
                INNER JOIN equipos e ON d.equipo_id = e.id
                WHERE d.acta_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$acta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🔍 📱 OBTENER LOS CELULARES DE UN ACTA (DETALLE LÍNEAS)
     */
    public function obtenerCelularesPorActaId($acta_id) {
        $sql = "SELECT apd.*, p.numero_celular, p.operador, p.nombre_plan, p.celular_marca, p.celular_modelo
                FROM actas_planes_detalle apd
                INNER JOIN planes_celulares p ON apd.plan_celular_id = p.id
                WHERE apd.acta_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$acta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🔍 🔌 OBTENER COMPONENTES INTERNOS DE UN EQUIPO ESPECÍFICO
     */
    public function getComponentesPorEquipo($equipo_id) {
        // 🌟 Se elimina 'serie' de la lista de campos para evitar el error 1054
        $sql = "SELECT id, tipo_componente, marca_modelo, capacidad_detalle, estado 
                FROM componentes_equipo 
                WHERE equipo_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$equipo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🔍 🔧 OBTENER TODOS LOS COMPONENTES VINCULADOS A LOS EQUIPOS DEL ACTA (USO GLOBAL PARA TABLA VISTA)
     */
    public function obtenerComponentesPorActaId($acta_id) {
        $sql = "SELECT ce.id, 
                       ce.tipo_componente AS nombre, 
                       '' AS serie, -- 🌟 Se envía vacío para mantener compatibilidad en el frontend
                       ce.marca_modelo AS marca, 
                       ce.estado AS estado_item, 
                       e.nombre AS equipo_padre,
                       ad.estado_entrega_equipo AS estado_entrega
                FROM componentes_equipo ce
                INNER JOIN actas_asignacion_detalle ad ON ce.equipo_id = ad.equipo_id
                INNER JOIN equipos e ON ce.equipo_id = e.id
                WHERE ad.acta_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$acta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * ⚡ GENERAR CÓDIGO CORRELATIVO ÚNICO PARA EL ACTA
     */
    public function generarCodigoActa() {
        $anioActual = date('Y');
        $sql = "SELECT COUNT(*) as total FROM actas_asignacion WHERE codigo_acta LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["ACTA-$anioActual-%"]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $secuencial = $resultado['total'] + 1;
        return "ACTA-" . $anioActual . "-" . str_pad($secuencial, 4, "0", STR_PAD_LEFT);
    }

    /**
     * ➕ CREAR UNA NUEVA ASIGNACIÓN EN LOTES
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // 1. Insertar Cabecera
            $sqlMaestro = "INSERT INTO actas_asignacion 
                            (codigo_acta, colaborador_id, usuario_id, fecha_entrega, estado_acta, observacion_general) 
                           VALUES (?, ?, ?, ?, 'Vigente', ?)";
            
            $stmtM = $this->db->prepare($sqlMaestro);
            $stmtM->execute([
                $data['codigo_acta'],
                $data['colaborador_id'],
                $data['usuario_id'],
                $data['fecha_entrega'],
                $data['observacion_general'] ?? null
            ]);

            $idNuevaAsignacion = $this->db->lastInsertId();

            // 2. Insertar Equipos (Ejecuta trg_equipo_asignado en cascada hacia 'equipos')
            if (!empty($data['equipos']) && is_array($data['equipos'])) {
                $sqlDetalleEq = "INSERT INTO actas_asignacion_detalle 
                                    (acta_id, equipo_id, estado_entrega_equipo, estado_item, observacion_item) 
                                   VALUES (?, ?, ?, 'En Uso', ?)";
                $stmtD = $this->db->prepare($sqlDetalleEq);

                foreach ($data['equipos'] as $item) {
                    if (!empty($item['equipo_id'])) {
                        $stmtD->execute([
                            $idNuevaAsignacion,
                            $item['equipo_id'],
                            $item['estado_entrega_equipo'] ?? 'Bueno',
                            $item['observacion_item'] ?? null
                        ]);
                    }
                }
            }

            // 3. 📱 Insertar Celulares (Ejecuta trg_plan_asignado)
            if (!empty($data['celulares']) && is_array($data['celulares'])) {
                $sqlDetalleCel = "INSERT INTO actas_planes_detalle 
                                    (acta_id, plan_celular_id, estado_entrega, observacion_entrega, estado_item) 
                                  VALUES (?, ?, ?, ?, 'En Uso')";
                $stmtC = $this->db->prepare($sqlDetalleCel);

                foreach ($data['celulares'] as $cel) {
                    if (!empty($cel['plan_celular_id'])) {
                        $stmtC->execute([
                            $idNuevaAsignacion,
                            $cel['plan_celular_id'],
                            $cel['estado_entrega'] ?? 'Bueno',
                            $cel['observacion_entrega'] ?? null
                        ]);
                    }
                }
            }

            AuditoriaModel::registrar('INSERT', 'actas_asignacion', $idNuevaAsignacion, null, $data);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en Asignacion::create -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * ↩️ ACTUALIZAR DEVOLUCIÓN INDIVIDUAL DE UN ACTIVO
     */
    public function updateItemDevolucion($detalle_id, $observacion_devolucion) {
        try {
            $fechaHoy = date('Y-m-d');
            $this->db->beginTransaction();

            // 1. Detalle de EQUIPOS (Ejecuta trg_equipo_devuelto)
            $sqlEq = "SELECT * FROM actas_asignacion_detalle WHERE id = ?";
            $stmtEq = $this->db->prepare($sqlEq);
            $stmtEq->execute([$detalle_id]);
            $itemEquipo = $stmtEq->fetch(PDO::FETCH_ASSOC);

            if ($itemEquipo) {
                $sql = "UPDATE actas_asignacion_detalle 
                        SET estado_item = 'Devuelto', 
                            fecha_devolucion_equipo = ?,
                            observacion_item = CONCAT(IFNULL(observacion_item, ''), ' | Devolución: ', ?)
                        WHERE id = ?";
                
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$fechaHoy, $observacion_devolucion, $detalle_id]);

                if ($result) {
                    $this->actualizarEstadoGlobalActa($itemEquipo['acta_id']);
                    AuditoriaModel::registrar('UPDATE', 'actas_asignacion_detalle', $detalle_id, $itemEquipo, ['estado_item' => 'Devuelto']);
                }
                
                $this->db->commit();
                return $result;
            }

            // 2. Detalle de LÍNEAS / CELULARES (Ejecuta trg_plan_devuelto)
            $sqlCel = "SELECT * FROM actas_planes_detalle WHERE id = ?";
            $stmtCel = $this->db->prepare($sqlCel);
            $stmtCel->execute([$detalle_id]);
            $itemCelular = $stmtCel->fetch(PDO::FETCH_ASSOC);

            if ($itemCelular) {
                $sql = "UPDATE actas_planes_detalle 
                        SET estado_item = 'Devuelto', 
                            fecha_devolucion = ?,
                            observacion_entrega = CONCAT(IFNULL(observacion_entrega, ''), ' | Devolución: ', ?)
                        WHERE id = ?";
                
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$fechaHoy, $observacion_devolucion, $detalle_id]);

                if ($result) {
                    $this->actualizarEstadoGlobalActa($itemCelular['acta_id']);
                    AuditoriaModel::registrar('UPDATE', 'actas_planes_detalle', $detalle_id, $itemCelular, ['estado_item' => 'Devuelto']);
                }

                $this->db->commit();
                return $result;
            }

            $this->db->rollBack();
            return false;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en Asignacion::updateItemDevolucion -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🗑️ ELIMINAR / ANULAR UN ACTA COMPLETA
     */
    public function delete($id) {
        try {
            $actaAntes = $this->find($id);
            if (!$actaAntes) return false;

            $stmtEstadoDisponible = $this->db->prepare("SELECT id FROM estados_equipo WHERE LOWER(nombre) = 'disponible' LIMIT 1");
            $stmtEstadoDisponible->execute();
            $idEstadoDisponible = $stmtEstadoDisponible->fetchColumn();

            if ($idEstadoDisponible === false) {
                return false;
            }

            $this->db->beginTransaction();

            if (!empty($actaAntes['equipos'])) {
                $sqlLiberarEq = "UPDATE equipos SET estado_id = ? WHERE id = ?";
                $stmtLE = $this->db->prepare($sqlLiberarEq);
                foreach ($actaAntes['equipos'] as $eq) {
                    $stmtLE->execute([(int)$idEstadoDisponible, $eq['equipo_id']]);
                }
            }

            if (!empty($actaAntes['celulares'])) {
                $sqlLiberarCel = "UPDATE planes_celulares SET estado_plan = 'Disponible' WHERE id = ?";
                $stmtLC = $this->db->prepare($sqlLiberarCel);
                foreach ($actaAntes['celulares'] as $cel) {
                    $stmtLC->execute([$cel['plan_celular_id']]);
                }
            }

            $stmt = $this->db->prepare("DELETE FROM actas_asignacion WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                AuditoriaModel::registrar('DELETE', 'actas_asignacion', $id, $actaAntes, null);
            }

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en Asignacion::delete -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * ⚡ RE-CALCULAR EL ESTADO GLOBAL DE LA CABECERA
     */
    private function actualizarEstadoGlobalActa($acta_id) {
        $sqlEq = "SELECT COUNT(*) as total, SUM(CASE WHEN estado_item = 'Devuelto' THEN 1 ELSE 0 END) as devueltos 
                  FROM actas_asignacion_detalle WHERE acta_id = ?";
        $stmtEq = $this->db->prepare($sqlEq);
        $stmtEq->execute([$acta_id]);
        $cEq = $stmtEq->fetch(PDO::FETCH_ASSOC);

        $sqlCel = "SELECT COUNT(*) as total, SUM(CASE WHEN estado_item = 'Devuelto' THEN 1 ELSE 0 END) as devueltos 
                   FROM actas_planes_detalle WHERE acta_id = ?";
        $stmtCel = $this->db->prepare($sqlCel);
        $stmtCel->execute([$acta_id]);
        $cCel = $stmtCel->fetch(PDO::FETCH_ASSOC);

        $totalActivos = ($cEq['total'] ?? 0) + ($cCel['total'] ?? 0);
        $totalDevueltos = ($cEq['devueltos'] ?? 0) + ($cCel['devueltos'] ?? 0);

        if ($totalActivos === $totalDevueltos && $totalActivos > 0) {
            $nuevoEstado = 'Finalizada';
            $fechaDevGlobal = ", fecha_devolucion_global = '" . date('Y-m-d') . "'";
        } else if ($totalDevueltos > 0) {
            $nuevoEstado = 'Parcial';
            $fechaDevGlobal = ", fecha_devolucion_global = NULL";
        } else {
            $nuevoEstado = 'Vigente';
            $fechaDevGlobal = ", fecha_devolucion_global = NULL";
        }

        $sqlUpdate = "UPDATE actas_asignacion 
                      SET estado_acta = :estado $fechaDevGlobal 
                      WHERE id = :acta_id";
        $sqlUpdateStmt = $this->db->prepare($sqlUpdate);
        $sqlUpdateStmt->execute([
            ':estado'  => $nuevoEstado,
            ':acta_id' => $acta_id
        ]);
    }

    /**
     * 📱 OBTENER CELULARES DISPONIBLES PARA ASIGNAR
     */
    public function getCelularesDisponibles() {
        $sql = "SELECT id, numero_celular, operador, nombre_plan, celular_marca, celular_modelo 
                FROM planes_celulares 
                WHERE estado_plan = 'Disponible' 
                ORDER BY numero_celular ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}