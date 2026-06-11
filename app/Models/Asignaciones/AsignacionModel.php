<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;
use Exception;

class AsignacionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene la cabecera del acta cruzada con el colaborador y el usuario técnico
     */
    public function obtenerActaPorId(int $id): array {
        try {
            $sql = "SELECT 
                        a.id,
                        a.codigo_acta,
                        a.fecha_entrega,
                        a.estado_acta,
                        a.observacion_general,
                        a.fecha_devolucion_global,
                        c.nombres AS colaborador_nombre,
                        c.correo AS colaborador_correo,
                        c.area AS colaborador_area,
                        c.cargo AS colaborador_cargo,
                        u.nombre AS usuario_tecnico
                    FROM actas_asignacion a
                    LEFT JOIN colaboradores c ON a.colaborador_id = c.id
                    LEFT JOIN usuarios u ON a.usuario_id = u.id
                    WHERE a.id = :id 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene todos los equipos informáticos vinculados E INYECTA SUS COMPONENTES
     */
    public function obtenerEquiposPorActaId(int $id_acta): array {
        try {
            // e.id AS equipo_id para poder buscar sus componentes internos
            $sql = "SELECT 
                        aad.id,
                        aad.estado_entrega_equipo,
                        aad.estado_item,
                        aad.observacion_item,
                        aad.fecha_devolucion_equipo,
                        e.id AS equipo_id,
                        e.nombre,
                        e.tipo,
                        e.serie, -- Esta serie pertenece al equipo (física), se mantiene si existe allí.
                        e.marca,
                        e.modelo
                    FROM actas_asignacion_detalle aad
                    INNER JOIN equipos e ON aad.equipo_id = e.id
                    WHERE aad.acta_id = :acta_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':acta_id' => $id_acta]);
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Recorremos cada equipo y le anexamos sus componentes internos
            if (!empty($equipos)) {
                foreach ($equipos as &$equipo) {
                    $equipo['componentes_internos'] = $this->obtenerComponentesPorEquipoId((int)$equipo['equipo_id']);
                }
            }

            return $equipos;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🔌 COMPONENTES INTERNOS: Limpio de la columna física 'serie'
     */
    public function obtenerComponentesPorEquipoId(int $equipo_id): array {
        try {
            // 🌟 QUITADA LA COLUMNA 'serie' DE ESTE SELECT PARA EVITAR EL ERROR INTERNO
            $sql = "SELECT id, tipo_componente, marca_modelo, capacidad_detalle, estado 
                    FROM componentes_equipo 
                    WHERE equipo_id = :equipo_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':equipo_id' => $equipo_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            // Muestra en los logs de Apache/XAMPP si algo más falla temporalmente
            error_log("Error en obtenerComponentesPorEquipoId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 📱 Obtiene los celulares/planes móviles
     */
    public function obtenerCelularesPorActaId(int $id_acta): array {
        try {
            $sql = "SELECT 
                        apd.id,
                        apd.estado_entrega AS estado_entrega_celular,
                        apd.estado_item,
                        apd.observacion_entrega AS observacion_item,
                        apd.fecha_devolucion AS fecha_devolucion_celular,
                        p.numero_celular,
                        p.operador,
                        p.nombre_plan,
                        p.celular_marca,
                        p.celular_modelo
                    FROM actas_planes_detalle apd
                    INNER JOIN planes_celulares p ON apd.plan_celular_id = p.id
                    WHERE apd.acta_id = :acta_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':acta_id' => $id_acta]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo celulares del acta: " . $e->getMessage());
            return [];
        }
    }
}