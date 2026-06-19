<?php

namespace App\Models\Componentes;

use App\Helpers\Database;
use App\Models\Auditoria\AuditoriaModel;

class ComponenteModel {

    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * LISTAR TODOS LOS COMPONENTES (CON FILTRO OPCIONAL)
     * Excluye componentes con borrado logico (estado = 'Eliminado').
     */
    public function getAll($equipo_id = null)
    {
        $sql = "
            SELECT
                c.id,
                c.equipo_id,
                c.tipo_componente AS tipo,
                c.marca_modelo,
                c.capacidad_detalle AS descripcion,
                CASE WHEN c.estado = 'Malo' THEN 'Danado' ELSE c.estado END AS estado,
                e.nombre AS equipo_nombre
            FROM componentes_equipo c
            INNER JOIN equipos e ON c.equipo_id = e.id
            WHERE c.estado <> 'Eliminado'
        ";

        if ($equipo_id) {
            $sql .= " AND c.equipo_id = ? ";
        }

        $sql .= " ORDER BY c.id DESC ";

        $stmt = $this->db->prepare($sql);

        if ($equipo_id) {
            $stmt->execute([$equipo_id]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // BUSCAR UN COMPONENTE POR ID
    public function find($id)
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                equipo_id,
                tipo_componente AS tipo,
                marca_modelo,
                capacidad_detalle AS descripcion,
                CASE WHEN estado = 'Malo' THEN 'Danado' ELSE estado END AS estado
            FROM componentes_equipo
            WHERE id = ?
        ");

        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // CREAR UN NUEVO COMPONENTE
    public function create($data)
    {
        $sql = "
            INSERT INTO componentes_equipo
            (equipo_id, tipo_componente, marca_modelo, capacidad_detalle, estado)
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);
        $estadoBD = (($data['estado'] ?? '') === 'Danado' || ($data['estado'] ?? '') === 'Dañado') ? 'Malo' : ($data['estado'] ?? 'Bueno');

        $result = $stmt->execute([
            $data['equipo_id'],
            $data['tipo'],
            $data['marca_modelo'] ?? 'Generico',
            $data['descripcion'],
            $estadoBD
        ]);

        if ($result) {
            $idNuevo = (int)$this->db->lastInsertId();
            AuditoriaModel::registrar('INSERT', 'componentes_equipo', $idNuevo, null, $this->find($idNuevo) ?: $data);
        }

        return $result;
    }

    // ACTUALIZAR UN COMPONENTE EXISTENTE
    public function update($id, $data)
    {
        $sql = "
            UPDATE componentes_equipo
            SET
                equipo_id = ?,
                tipo_componente = ?,
                marca_modelo = ?,
                capacidad_detalle = ?,
                estado = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $estadoBD = (($data['estado'] ?? '') === 'Danado' || ($data['estado'] ?? '') === 'Dañado') ? 'Malo' : ($data['estado'] ?? 'Bueno');
        $antes = $this->find($id);

        $result = $stmt->execute([
            $data['equipo_id'],
            $data['tipo'],
            $data['marca_modelo'] ?? 'Generico',
            $data['descripcion'],
            $estadoBD,
            $id
        ]);

        if ($result && $antes) {
            AuditoriaModel::registrar('UPDATE', 'componentes_equipo', (int)$id, $antes, $this->find($id) ?: $data);
        }

        return $result;
    }

    /**
     * ELIMINAR UN COMPONENTE (BORRADO LOGICO)
     */
    public function delete($id)
    {
        try {
            $antes = $this->find($id);

            $stmt = $this->db->prepare("
                UPDATE componentes_equipo
                SET estado = 'Eliminado'
                WHERE id = ?
            ");

            $result = $stmt->execute([$id]);

            if ($result && $antes) {
                AuditoriaModel::registrar('DELETE', 'componentes_equipo', (int)$id, $antes, ['estado' => 'Eliminado']);
            }

            return $result;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * CONTAR TOTAL DE COMPONENTES (CON FILTRO OPCIONAL)
     */
    public function count($equipo_id = null)
    {
        $sql = "SELECT COUNT(*) as total FROM componentes_equipo WHERE estado <> 'Eliminado'";

        if ($equipo_id) {
            $sql .= " AND equipo_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$equipo_id]);
        } else {
            $stmt = $this->db->query($sql);
        }

        $result = $stmt->fetch();
        return $result['total'];
    }
}
