<?php

namespace App\Models\Dashboard;

use App\Helpers\Database;
use PDO;

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // TOTAL EQUIPOS
    // 🛡️ MODIFICADO: Excluye del conteo general los equipos borrados lógicamente
    public function totalEquipos()
    {
        $sql = "SELECT COUNT(*) as total 
                FROM equipos e
                LEFT JOIN estados_equipo es ON e.estado_id = es.id
                WHERE es.nombre IS NULL OR es.nombre <> 'Eliminado'";
        $stmt = $this->db->query($sql);

        return $stmt->fetch()['total'];
    }

    // EQUIPOS ASIGNADOS (Corregido: Lee el detalle de las actas en uso)
    public function equiposAsignados()
    {
        $sql = "SELECT COUNT(*) as total 
                FROM actas_asignacion_detalle 
                WHERE estado_item = 'En Uso'";

        $stmt = $this->db->query($sql);

        return $stmt->fetch()['total'];
    }

    // TOTAL COLABORADORES
    public function totalColaboradores()
    {
        $sql = "SELECT COUNT(*) as total 
                FROM colaboradores 
                WHERE estado = 1";

        $stmt = $this->db->query($sql);

        return $stmt->fetch()['total'];
    }

    // EQUIPOS EN MANTENIMIENTO
    public function equiposMantenimiento()
    {
        $sql = "SELECT COUNT(*) as total
                FROM equipos e
                INNER JOIN estados_equipo es ON e.estado_id = es.id
                WHERE es.nombre = 'MANTENIMIENTO'";

        $stmt = $this->db->query($sql);

        return $stmt->fetch()['total'];
    }

    /**
     * Cuenta los componentes agrupados por su estado físico (Bueno, Regular, Malo)
     * 🛡️ MODIFICADO: Ignora por completo los componentes con estado 'Eliminado'
     */
    public function infoComponentes()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'Bueno' THEN 1 ELSE 0 END) as buenos,
                    SUM(CASE WHEN estado = 'Regular' THEN 1 ELSE 0 END) as regulares,
                    SUM(CASE WHEN estado = 'Malo' THEN 1 ELSE 0 END) as danados
                FROM componentes_equipo
                WHERE estado <> 'Eliminado'";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Trae las últimas asignaciones cruzadas con colaboradores y equipos 
     * para el feed en tiempo real de la pantalla de inicio.
     */
    public function ultimasAsignaciones($limite = 3)
    {
        $sql = "SELECT 
                    a.id as acta_id,
                    a.codigo_acta,
                    a.fecha_entrega,
                    ad.estado_item,
                    c.nombres as colaborador_nombre, 
                    e.nombre as equipo_nombre, 
                    e.serie as equipo_serie
                FROM actas_asignacion_detalle ad
                INNER JOIN actas_asignacion a ON ad.acta_id = a.id
                INNER JOIN colaboradores c ON a.colaborador_id = c.id
                INNER JOIN equipos e ON ad.equipo_id = e.id
                ORDER BY a.fecha_entrega DESC, ad.id DESC
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trae las órdenes de mantenimiento críticas activas (Pendientes o En Proceso)
     * 🛡️ MODIFICADO: Excluye órdenes vinculadas a equipos borrados lógicamente
     */
    public function mantenimientosCriticos($limite = 3)
    {
        $sql = "SELECT m.*, e.nombre as equipo_nombre
                FROM mantenimientos m
                INNER JOIN equipos e ON m.equipo_id = e.id
                LEFT JOIN estados_equipo es ON e.estado_id = es.id
                WHERE m.estado IN ('Pendiente', 'En Proceso')
                  AND (es.nombre IS NULL OR es.nombre <> 'Eliminado')
                ORDER BY m.fecha_ingreso DESC
                LIMIT :limite";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}