<?php

namespace App\Models\Inventario;

use App\Helpers\Database;
use PDO;
use Exception;

class InventarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene el inventario consolidado unificando Equipos y Celulares en un solo listado
     * @return array
     */
    public function obtenerInventarioGeneral(): array {
        try {
            $sql = "SELECT 
                        e.id,
                        e.tipo,
                        e.nombre AS equipo_nombre,
                        e.marca,
                        e.modelo,
                        e.serie,
                        ee.nombre AS estado_nombre,
                        e.estado_id,
                        c.nombres AS colaborador_asignado,
                        c.area AS colaborador_area,
                        (SELECT COUNT(*) FROM componentes_equipo ce WHERE ce.equipo_id = e.id) as total_componentes
                    FROM equipos e
                    INNER JOIN estados_equipo ee ON e.estado_id = ee.id
                    LEFT JOIN actas_asignacion_detalle aad ON e.id = aad.equipo_id AND aad.estado_item = 'En Uso'
                    LEFT JOIN actas_asignacion aa ON aad.acta_id = aa.id AND aa.estado_acta = 'Vigente'
                    LEFT JOIN colaboradores c ON aa.colaborador_id = c.id

                    UNION ALL

                    SELECT 
                        p.id,
                        'Celular' AS tipo,
                        CONCAT('Celular ', p.operador, ' - ', p.nombre_plan) AS equipo_nombre,
                        p.celular_marca AS marca,
                        p.celular_modelo AS modelo,
                        p.numero_celular AS serie,
                        p.estado_plan AS estado_nombre,
                        CASE 
                            WHEN p.estado_plan = 'Disponible' THEN 1
                            WHEN p.estado_plan = 'Asignado' THEN 2
                            WHEN p.estado_plan = 'Mantenimiento' THEN 3
                            ELSE 4
                        END AS estado_id,
                        c.nombres AS colaborador_asignado,
                        c.area AS colaborador_area,
                        0 as total_componentes
                    FROM planes_celulares p
                    LEFT JOIN actas_planes_detalle apd ON p.id = apd.plan_celular_id AND apd.estado_item = 'En Uso'
                    LEFT JOIN actas_asignacion aa ON apd.acta_id = aa.id AND aa.estado_acta = 'Vigente'
                    LEFT JOIN colaboradores c ON aa.colaborador_id = c.id
                    
                    ORDER BY id DESC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error en obtenerInventarioGeneral: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las métricas unificadas (Equipos + Celulares) calculadas sobre la base de datos
     * @return array
     */
    public function obtenerMetricasReporte(): array {
        try {
            // Consulta consolidada para contar tanto infraestructura de equipos como celulares
            $sql = "SELECT
                        -- Totales globales de ambas tablas
                        (SELECT COUNT(*) FROM equipos) + (SELECT COUNT(*) FROM planes_celulares) as total_equipos,
                        
                        -- Sumatoria de Disponibles
                        (SELECT COUNT(*) FROM equipos WHERE estado_id = 1) + 
                        (SELECT COUNT(*) FROM planes_celulares WHERE estado_plan = 'Disponible') as disponibles,
                        
                        -- Sumatoria de Asignados
                        (SELECT COUNT(*) FROM equipos WHERE estado_id = 2) + 
                        (SELECT COUNT(*) FROM planes_celulares WHERE estado_plan = 'Asignado') as asignados,
                        
                        -- Sumatoria en Mantenimiento
                        (SELECT COUNT(*) FROM equipos WHERE estado_id = 3) + 
                        (SELECT COUNT(*) FROM planes_celulares WHERE estado_plan = 'Mantenimiento') as mantenimiento,
                        
                        -- Sumatoria de Bajas/Daños/Robos
                        (SELECT COUNT(*) FROM equipos WHERE estado_id IN (4,5,6)) + 
                        (SELECT COUNT(*) FROM planes_celulares WHERE estado_plan IN ('Baja','Robado/Perdido')) as bajas_danos";
            
            $result = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: [
                'total_equipos' => 0, 'disponibles' => 0, 
                'asignados' => 0, 'mantenimiento' => 0, 'bajas_danos' => 0
            ];
        } catch (Exception $e) {
            error_log("Error en obtenerMetricasReporte: " . $e->getMessage());
            return [
                'total_equipos' => 0, 'disponibles' => 0, 
                'asignados' => 0, 'mantenimiento' => 0, 'bajas_danos' => 0
            ];
        }
    }
}