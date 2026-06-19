<?php

namespace App\Models\Inventario;

use App\Helpers\Database;
use PDO;
use Exception;

class EquiposAsignadosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene de forma combinada los equipos tecnológicos y celulares asignados
     * manteniendo los alias originales exactos del Frontend para evitar que se rompa la vista.
     */
    public function obtenerEquiposAsignados(): array {
        try {
            $sql = "SELECT 
                        e.id AS equipo_id,
                        e.tipo AS equipo_tipo,
                        e.nombre AS equipo_nombre,
                        e.marca AS equipo_marca,
                        e.modelo AS equipo_modelo,
                        e.serie AS equipo_serie,
                        c.nombres AS colaborador_nombre,
                        c.cargo AS colaborador_cargo,
                        c.area AS colaborador_area,
                        c.correo AS colaborador_correo,
                        aa.codigo_acta,
                        aa.fecha_entrega,
                        aad.estado_entrega_equipo,
                        aad.observacion_item
                    FROM equipos e
                    INNER JOIN estados_equipo ee ON e.estado_id = ee.id
                    INNER JOIN actas_asignacion_detalle aad ON e.id = aad.equipo_id 
                        AND aad.estado_item = 'En Uso'
                    INNER JOIN actas_asignacion aa ON aad.acta_id = aa.id 
                        AND aa.estado_acta = 'Vigente'
                    INNER JOIN colaboradores c ON aa.colaborador_id = c.id
                    WHERE LOWER(ee.nombre) = 'asignado'

                    UNION ALL

                    SELECT 
                        p.id AS equipo_id,
                        'Celular' AS equipo_tipo,            -- Mapeado a la columna Tipo
                        p.nombre_plan AS equipo_nombre,       -- Mapeado a la columna Nombre
                        p.celular_marca AS equipo_marca,     -- Mapeado a la columna Marca
                        p.celular_modelo AS equipo_modelo,   -- Mapeado a la columna Modelo
                        CONCAT(p.numero_celular, ' (', p.celular_imei_1, ')') AS equipo_serie, -- Mapeado a Serie (Muestra Número e IMEI)
                        c.nombres AS colaborador_nombre,
                        c.cargo AS colaborador_cargo,
                        c.area AS colaborador_area,
                        c.correo AS colaborador_correo,
                        aa.codigo_acta,
                        aa.fecha_entrega,
                        apd.estado_entrega AS estado_entrega_equipo, -- Mapeado al alias original
                        apd.observacion_entrega AS observacion_item  -- Mapeado al alias original
                    FROM planes_celulares p
                    INNER JOIN actas_planes_detalle apd ON p.id = apd.plan_celular_id 
                        AND apd.estado_item = 'En Uso'
                    INNER JOIN actas_asignacion aa ON apd.acta_id = aa.id 
                        AND aa.estado_acta = 'Vigente'
                    INNER JOIN colaboradores c ON aa.colaborador_id = c.id
                    WHERE p.estado_plan = 'Asignado'

                    ORDER BY fecha_entrega DESC, equipo_id DESC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene métricas específicas del personal con asignaciones activas (Equipos + Celulares)
     */
    public function obtenerMetricasAsignados(): array {
        try {
            // Contar equipos tradicionales asignados
            $sqlEquipos = "SELECT 
                                COUNT(DISTINCT e.id) as total_equipos,
                                SUM(CASE WHEN e.tipo = 'Laptop' THEN 1 ELSE 0 END) as total_laptops,
                                SUM(CASE WHEN e.tipo = 'Desktop' THEN 1 ELSE 0 END) as total_desktops
                           FROM equipos e
                          INNER JOIN estados_equipo ee ON e.estado_id = ee.id
                           INNER JOIN actas_asignacion_detalle aad ON e.id = aad.equipo_id AND aad.estado_item = 'En Uso'
                           INNER JOIN actas_asignacion aa ON aad.acta_id = aa.id AND aa.estado_acta = 'Vigente'
                          WHERE LOWER(ee.nombre) = 'asignado'";
            
            $resEquipos = $this->db->query($sqlEquipos)->fetch(PDO::FETCH_ASSOC) ?: ['total_equipos' => 0, 'total_laptops' => 0, 'total_desktops' => 0];

            // Contar celulares asignados
            $sqlCelulares = "SELECT COUNT(DISTINCT p.id) as total_celulares 
                             FROM planes_celulares p
                             INNER JOIN actas_planes_detalle apd ON p.id = apd.plan_celular_id AND apd.estado_item = 'En Uso'
                             INNER JOIN actas_asignacion aa ON apd.acta_id = aa.id AND aa.estado_acta = 'Vigente'
                             WHERE p.estado_plan = 'Asignado'";
            
            $resCelulares = $this->db->query($sqlCelulares)->fetch(PDO::FETCH_ASSOC) ?: ['total_celulares' => 0];

            // Colaboradores únicos con cualquier activo bajo su poder
            $sqlColab = "SELECT COUNT(DISTINCT colaborador_id) as total_colab FROM (
                            SELECT aa.colaborador_id FROM actas_asignacion aa 
                            INNER JOIN actas_asignacion_detalle aad ON aa.id = aad.acta_id AND aad.estado_item = 'En Uso' WHERE aa.estado_acta = 'Vigente'
                            UNION
                            SELECT aa.colaborador_id FROM actas_asignacion aa 
                            INNER JOIN actas_planes_detalle apd ON aa.id = apd.acta_id AND apd.estado_item = 'En Uso' WHERE aa.estado_acta = 'Vigente'
                         ) as asignaciones_totales";
            
            $resColab = $this->db->query($sqlColab)->fetch(PDO::FETCH_ASSOC) ?: ['total_colab' => 0];

            return [
                'total_asignados' => ($resEquipos['total_equipos'] + $resCelulares['total_celulares']),
                'colaboradores_con_activos' => $resColab['total_colab'],
                'total_laptops' => $resEquipos['total_laptops'],
                'total_desktops' => $resEquipos['total_desktops']
            ];
        } catch (Exception $e) {
            return ['total_asignados' => 0, 'colaboradores_con_activos' => 0, 'total_laptops' => 0, 'total_desktops' => 0];
        }
    }
}