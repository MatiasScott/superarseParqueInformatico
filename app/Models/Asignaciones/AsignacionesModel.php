<?php

namespace App\Models\Asignaciones;

use App\Helpers\Database;
use PDO;
use Exception;
use App\Models\Auditoria\AuditoriaModel;
use App\Models\Equipos\Equipo; // ⚡ SE IMPORTA EL MODELO DE EQUIPOS PARA EL CAMBIO DE ESTADOS

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
 WHERE ad.acta_id = a.id
   AND ce.estado NOT IN ('Eliminado', 'Malo')) as total_componentes
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
    $sql = "SELECT id, tipo_componente, marca_modelo, capacidad_detalle,
                   CASE WHEN estado = 'Malo' THEN 'De baja' ELSE estado END AS estado
            FROM componentes_equipo 
            WHERE equipo_id = ?
              AND estado != 'Eliminado'"; // 👈 🚫 FILTRO AGREGADO AQUÍ
    
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
                   CASE WHEN ce.estado = 'Malo' THEN 'De baja' ELSE ce.estado END AS estado_item,
                   e.nombre AS equipo_padre,
                   ad.estado_entrega_equipo AS estado_entrega
            FROM componentes_equipo ce
            INNER JOIN actas_asignacion_detalle ad ON ce.equipo_id = ad.equipo_id
            INNER JOIN equipos e ON ce.equipo_id = e.id
            WHERE ad.acta_id = ?
              AND ce.estado != 'Eliminado'"; // 👈 🚫 FILTRO AGREGADO AQUÍ
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$acta_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

  /**
 * ⚡ GENERAR CÓDIGO CORRELATIVO ÚNICO PARA EL ACTA (CORREGIDO)
 */
public function generarCodigoActa() {
    $anioActual = date('Y');
    
    // Consulta para obtener el último código registrado en el año actual por ID
    $sql = "SELECT codigo_acta 
            FROM actas_asignacion 
            WHERE codigo_acta LIKE ? 
            ORDER BY id DESC 
            LIMIT 1";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ACTA-$anioActual-%"]);
    $ultimoCodigo = $stmt->fetchColumn();

    if ($ultimoCodigo) {
        // Extrae el número del final de la cadena 'ACTA-2026-0061' => 61
        $partes = explode('-', $ultimoCodigo);
        $secuencial = ((int) end($partes)) + 1;
    } else {
        // Si no hay actas en el año actual, empieza en 1
        $secuencial = 1;
    }

    return "ACTA-" . $anioActual . "-" . str_pad($secuencial, 4, "0", STR_PAD_LEFT);
}
    /**
     * ➕ CREAR UNA NUEVA ASIGNACIÓN EN LOTES
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            if (empty($data['usuario_id'])) {
                $stmtUser = $this->db->query("SELECT id FROM usuarios LIMIT 1");
                $idUsuarioRespaldo = $stmtUser->fetchColumn();

                if ($idUsuarioRespaldo === false) {
                    throw new Exception("No se puede crear el acta porque no existe ningún usuario registrado.");
                }
                
                $data['usuario_id'] = $idUsuarioRespaldo;
            }

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

            // 2. Insertar Equipos
            if (!empty($data['equipos']) && is_array($data['equipos'])) {
                $sqlDetalleEq = "INSERT INTO actas_asignacion_detalle 
                                    (acta_id, equipo_id, estado_entrega_equipo, estado_item, observacion_item) 
                                   VALUES (?, ?, ?, 'En Uso', ?)";
                $stmtD = $this->db->prepare($sqlDetalleEq);
                $equipoModel = new Equipo();

                foreach ($data['equipos'] as $item) {
                    if (!empty($item['equipo_id'])) {
                        $stmtD->execute([
                            $idNuevaAsignacion,
                            $item['equipo_id'],
                            $item['estado_entrega_equipo'] ?? 'Bueno',
                            $item['observacion_item'] ?? null
                        ]);
                        $equipoModel->marcarComoAsignado($item['equipo_id']);
                    }
                }
            }

            // 3. 📱 Insertar Celulares
            if (!empty($data['celulares']) && is_array($data['celulares'])) {
                $sqlDetalleCel = "INSERT INTO actas_planes_detalle 
                                    (acta_id, plan_celular_id, estado_entrega, observacion_entrega, estado_item) 
                                  VALUES (?, ?, ?, ?, 'En Uso')";
                $stmtC = $this->db->prepare($sqlDetalleCel);
                
                // ⚡ CORRECCIÓN: Instancia con namespace absoluto para evitar el Error 500
                $celularModel = new \App\Models\Celular\PlanesCelularesModel();

                foreach ($data['celulares'] as $cel) {
                    if (!empty($cel['plan_celular_id'])) {
                        $stmtC->execute([
                            $idNuevaAsignacion,
                            $cel['plan_celular_id'],
                            $cel['estado_entrega'] ?? 'Bueno',
                            $cel['observacion_entrega'] ?? null
                        ]);

                        // Cambia automáticamente el estado de la línea a 'Asignado'
                        $celularModel->marcarComoAsignado($cel['plan_celular_id']);
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
     * 💻 ACTUALIZAR DEVOLUCIÓN INDIVIDUAL DE EQUIPOS DE CÓMPUTO
     */
    public function updateItemDevolucion($detalle_id, $observacion_devolucion) {
        try {
            $fechaHoy = date('Y-m-d');
            $this->db->beginTransaction();

            // Buscar únicamente en detalles de equipos
            $sqlEq = "SELECT * FROM actas_asignacion_detalle WHERE id = ?";
            $stmtEq = $this->db->prepare($sqlEq);
            $stmtEq->execute([$detalle_id]);
            $itemEquipo = $stmtEq->fetch(PDO::FETCH_ASSOC);

            if (!$itemEquipo) {
                $this->db->rollBack();
                return false;
            }

            $sql = "UPDATE actas_asignacion_detalle 
                    SET estado_item = 'Devuelto', 
                        fecha_devolucion_equipo = ?,
                        observacion_item = CONCAT(IFNULL(observacion_item, ''), ' | Devolución: ', ?)
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$fechaHoy, $observacion_devolucion, $detalle_id]);

            if ($result) {
                if (!empty($itemEquipo['equipo_id'])) {
                    // Restaurar estado de componentes a 'Bueno'
                    $sqlComp = "UPDATE componentes_equipo 
                                SET estado = 'Bueno' 
                                WHERE equipo_id = ? AND estado <> 'Eliminado'";
                    $stmtComp = $this->db->prepare($sqlComp);
                    $stmtComp->execute([$itemEquipo['equipo_id']]);

                    // Liberar el equipo de cómputo
                    $equipoModel = new Equipo();
                    $equipoModel->marcarComoDisponible($itemEquipo['equipo_id']);
                }

                $this->actualizarEstadoGlobalActa($itemEquipo['acta_id']);
                AuditoriaModel::registrar('UPDATE', 'actas_asignacion_detalle', $detalle_id, $itemEquipo, ['estado_item' => 'Devuelto']);
            }
            
            $this->db->commit();
            return (bool)$result;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error en Asignacion::updateItemDevolucion -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📱 ACTUALIZAR DEVOLUCIÓN INDIVIDUAL DE LÍNEAS / CELULARES
     */
    public function updateCelularDevolucion($detalle_id, $observacion_devolucion) {
        try {
            $fechaHoy = date('Y-m-d');
            $this->db->beginTransaction();

            // Buscar únicamente en detalles de telefonía/planes
            $sqlCel = "SELECT * FROM actas_planes_detalle WHERE id = ?";
            $stmtCel = $this->db->prepare($sqlCel);
            $stmtCel->execute([$detalle_id]);
            $itemCelular = $stmtCel->fetch(PDO::FETCH_ASSOC);

            if (!$itemCelular) {
                $this->db->rollBack();
                return false;
            }

            $sql = "UPDATE actas_planes_detalle 
                    SET estado_item = 'Devuelto', 
                        fecha_devolucion = ?,
                        observacion_entrega = CONCAT(IFNULL(observacion_entrega, ''), ' | Devolución: ', ?)
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$fechaHoy, $observacion_devolucion, $detalle_id]);

            if ($result) {
                if (!empty($itemCelular['plan_celular_id'])) {
                    $sqlLiberar = "UPDATE planes_celulares SET estado_plan = 'Disponible' WHERE id = ?";
                    $stmtLiberar = $this->db->prepare($sqlLiberar);
                    $stmtLiberar->execute([$itemCelular['plan_celular_id']]);
                }

                $this->actualizarEstadoGlobalActa($itemCelular['acta_id']);
                AuditoriaModel::registrar('UPDATE', 'actas_planes_detalle', $detalle_id, $itemCelular, ['estado_item' => 'Devuelto']);
            }

            $this->db->commit();
            return (bool)$result;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error en Asignacion::updateCelularDevolucion -> " . $e->getMessage());
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