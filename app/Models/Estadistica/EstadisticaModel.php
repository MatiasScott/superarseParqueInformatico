<?php

namespace App\Models\Estadistica;

use App\Helpers\Database;
use PDO;
use Exception;

class EstadisticaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * 🖥️ MÉTRICAS GENERALES Y FILTRADAS DE EQUIPOS
     */
    public function getEstadisticasEquipos(?string $tipoEquipo = null, ?int $sedeId = null, ?string $modelo = null): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($tipoEquipo)) {
                $whereClauses[] = "e.tipo = :tipo";
                $params[':tipo'] = $tipoEquipo;
            }
            if (!empty($sedeId)) {
                $whereClauses[] = "e.sede_id = :sede_id";
                $params[':sede_id'] = $sedeId;
            }
            if (!empty($modelo)) {
                $whereClauses[] = "e.modelo LIKE :modelo";
                $params[':modelo'] = '%' . $modelo . '%';
            }

            $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

            $sql = "SELECT 
                        COUNT(e.id) as total_equipos,
                        IFNULL(SUM(e.precio), 0) as inversion_total,
                        IFNULL(AVG(e.precio), 0) as precio_promedio,
                        IFNULL(MIN(e.precio), 0) as precio_minimo,
                        IFNULL(MAX(e.precio), 0) as precio_maximo
                    FROM equipos e
                    $whereSql";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 📋 LISTADO COMPLETO CRUZADO PARA EL REPORTE EN TABLA Y PDF
     */
    public function getListadoEquiposReporte(?string $tipoEquipo = null, ?int $sedeId = null, ?string $modelo = null): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($tipoEquipo)) { $whereClauses[] = "e.tipo = :tipo"; $params[':tipo'] = $tipoEquipo; }
            if (!empty($sedeId)) { $whereClauses[] = "e.sede_id = :sede_id"; $params[':sede_id'] = $sedeId; }
            if (!empty($modelo)) { $whereClauses[] = "e.modelo LIKE :modelo"; $params[':modelo'] = '%' . $modelo . '%'; }

            $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

            $sql = "SELECT e.id, e.tipo, e.nombre, e.marca, e.modelo, e.serie, e.precio, 
                           ee.nombre as estado_nombre, s.nombre as sede_nombre
                    FROM equipos e
                    INNER JOIN estados_equipo ee ON e.estado_id = ee.id
                    INNER JOIN sedes s ON e.sede_id = s.id
                    $whereSql 
                    ORDER BY e.tipo ASC, e.precio DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🏢 CATÁLOGO DE SEDES (Para llenar el select del filtro)
     */
    public function obtenerSedesDisponibles(): array {
        try {
            $sql = "SELECT id, nombre FROM sedes ORDER BY nombre ASC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 📊 INVERSIÓN TOTAL AGRUPADA POR TIPO DE HARDWARE
     */
    public function getInversionPorTipo(): array {
        try {
            $sql = "SELECT e.tipo, COUNT(e.id) as cantidad, IFNULL(SUM(e.precio), 0) as inversion_subtotal 
                    FROM equipos e GROUP BY e.tipo ORDER BY inversion_subtotal DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🛠️ MÉTRICAS GLOBALES DE COMPONENTES INTERNOS (Soportando Filtro por Sede)
     */
    public function getEstadisticasComponentes(?int $sedeId = null): array {
        try {
            $whereSql = "";
            $params = [];

            if (!empty($sedeId) && $sedeId > 0) {
                $whereSql = "WHERE e.sede_id = :sede_id";
                $params[':sede_id'] = $sedeId;
            }

            $sql = "SELECT 
                        COUNT(ce.id) as total_componentes,
                        SUM(CASE WHEN ce.tipo_componente = 'RAM' THEN 1 ELSE 0 END) as total_ram,
                        SUM(CASE WHEN ce.tipo_componente IN ('SSD', 'Disco Duro') THEN 1 ELSE 0 END) as total_almacenamiento,
                        SUM(CASE WHEN ce.estado = 'Bueno' THEN 1 ELSE 0 END) as componentes_buenos
                    FROM componentes_equipo ce
                    INNER JOIN equipos e ON ce.equipo_id = e.id
                    $whereSql";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_componentes' => 0, 'total_ram' => 0, 'total_almacenamiento' => 0, 'componentes_buenos' => 0];
        } catch (Exception $e) {
            return ['total_componentes' => 0, 'total_ram' => 0, 'total_almacenamiento' => 0, 'componentes_buenos' => 0];
        }
    }

    /**
     * 📊 DISTRIBUCIÓN Y CANTIDAD POR TIPO DE COMPONENTE (Para Gráfico Polar/Barras)
     */
    public function getCantidadPorComponente(?int $sedeId = null): array {
        try {
            $whereSql = "";
            $params = [];

            if (!empty($sedeId) && $sedeId > 0) {
                $whereSql = "WHERE e.sede_id = :sede_id";
                $params[':sede_id'] = $sedeId;
            }

            $sql = "SELECT ce.tipo_componente, COUNT(ce.id) as cantidad
                    FROM componentes_equipo ce
                    INNER JOIN equipos e ON ce.equipo_id = e.id
                    $whereSql
                    GROUP BY ce.tipo_componente 
                    ORDER BY cantidad DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🧩 SUB-CONSULTA ADJUNTABLE: COMPONENTES ESPECÍFICOS DE UN EQUIPO
     */
    public function getComponentesPorEquipo(int $equipoId): array {
        try {
            $sql = "SELECT tipo_componente, marca_modelo, capacidad_detalle, serie, estado 
                    FROM componentes_equipo 
                    WHERE equipo_id = :equipo_id 
                    ORDER BY tipo_componente ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':equipo_id' => $equipoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 📱 MÉTRICAS FILTRADAS PARA PLANES CELULARES
     */
    public function getEstadisticasCelulares(?int $sedeId = null, ?string $operador = null): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($sedeId) && $sedeId > 0) {
                $whereClauses[] = "c.sede_id = :sede_id";
                $params[':sede_id'] = $sedeId;
            }
            if (!empty($operador)) {
                $whereClauses[] = "p.operador = :operador";
                $params[':operador'] = $operador;
            }

            $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

            $sql = "SELECT 
                        COUNT(p.id) as total_lineas, 
                        IFNULL(SUM(p.costo_mensual), 0) as gasto_mensual_total,
                        IFNULL(AVG(p.costo_mensual), 0) as costo_medio_plan
                    FROM planes_celulares p
                    LEFT JOIN actas_planes_detalle apd ON p.id = apd.plan_celular_id AND apd.estado_item = 'En Uso'
                    LEFT JOIN actas_asignacion aa ON apd.acta_id = aa.id
                    LEFT JOIN colaboradores c ON aa.colaborador_id = c.id
                    $whereSql";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_lineas' => 0, 'gasto_mensual_total' => 0, 'costo_medio_plan' => 0];
        } catch (Exception $e) {
            return ['total_lineas' => 0, 'gasto_mensual_total' => 0, 'costo_medio_plan' => 0];
        }
    }

    /**
     * 📊 DISTRIBUCIÓN DE GASTO POR OPERADOR 
     */
    public function getGastoPorOperador(?int $sedeId = null): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($sedeId) && $sedeId > 0) {
                $whereClauses[] = "c.sede_id = :sede_id";
                $params[':sede_id'] = $sedeId;
            }

            $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

            $sql = "SELECT 
                        p.operador, 
                        COUNT(p.id) as cantidad_lineas, 
                        IFNULL(SUM(p.costo_mensual), 0) as gasto_subtotal 
                    FROM planes_celulares p
                    LEFT JOIN actas_planes_detalle apd ON p.id = apd.plan_celular_id AND apd.estado_item = 'En Uso'
                    LEFT JOIN actas_asignacion aa ON apd.acta_id = aa.id
                    LEFT JOIN colaboradores c ON aa.colaborador_id = c.id
                    $whereSql
                    GROUP BY p.operador
                    ORDER BY gasto_subtotal DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 📋 LISTADO DE PLANES Y LÍNEAS PARA LA NUEVA TABLA (MAPEADO EXACTO)
     */
    public function getListadoPlanesReporte(?int $sedeId = null, ?string $operador = null): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($sedeId) && $sedeId > 0) {
                $whereClauses[] = "c.sede_id = :sede_id";
                $params[':sede_id'] = $sedeId;
            }
            if (!empty($operador)) {
                $whereClauses[] = "p.operador = :operador";
                $params[':operador'] = $operador;
            }

            $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

            $sql = "SELECT 
                        p.id, 
                        p.numero_celular, 
                        p.operador, 
                        p.nombre_plan, 
                        p.costo_mensual, 
                        p.estado_plan,
                        p.celular_marca,
                        p.celular_modelo,
                        IFNULL(s.nombre, 'DISPONIBLE (En Bodega)') as sede_nombre,
                        IFNULL(c.nombres, 'Sin asignar') as responsable
                    FROM planes_celulares p
                    LEFT JOIN actas_planes_detalle apd ON p.id = apd.plan_celular_id AND apd.estado_item = 'En Uso'
                    LEFT JOIN actas_asignacion aa ON apd.acta_id = aa.id
                    LEFT JOIN colaboradores c ON aa.colaborador_id = c.id
                    LEFT JOIN sedes s ON c.sede_id = s.id
                    $whereSql 
                    ORDER BY p.costo_mensual DESC, p.operador ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}