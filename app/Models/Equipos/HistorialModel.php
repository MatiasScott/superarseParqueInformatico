<?php

namespace App\Models\Equipos;

use App\Helpers\Database;
use PDO;

class HistorialModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getHistorialCompleto() {
        $sql = "
            SELECT 
                h.id,
                h.tipo_movimiento,
                h.fecha,
                h.observacion,
                e.nombre AS equipo_nombre,
                e.serie AS equipo_serie,
                e.marca AS equipo_marca,
                c.nombres AS colaborador_nombre
            FROM historial_equipos h
            INNER JOIN equipos e ON h.equipo_id = e.id
            LEFT JOIN colaboradores c ON h.colaborador_id = c.id
            ORDER BY h.fecha DESC
        ";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}