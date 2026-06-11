<?php

namespace App\Models\Equipos;

use App\Helpers\Database;
use PDO;

class BajasModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getEquiposDeBaja() {
        $sql = "SELECT e.*, est.nombre as ultimo_estado 
                FROM equipos e
                LEFT JOIN estados_equipo est ON e.estado_id = est.id
                WHERE e.fecha_baja IS NOT NULL
                ORDER BY e.fecha_baja DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}