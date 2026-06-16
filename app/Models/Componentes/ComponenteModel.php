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
     * 🛡️ MODIFICADO: Excluye componentes con borrado lógico (estado = 'Eliminado')
        $result = $stmt->execute([
    public function getAll($equipo_id = null)
    {
        $sql = "
            SELECT 
                c.id,
                c.equipo_id,

        if ($result) {
            $idNuevo = (int)$this->db->lastInsertId();
            AuditoriaModel::registrar('INSERT', 'componentes_equipo', $idNuevo, null, $this->find($idNuevo) ?: $data);
        }

        return $result;
                c.tipo_componente AS tipo,
                c.marca_modelo,
                c.capacidad_detalle AS descripcion,
                CASE WHEN c.estado = 'Malo' THEN 'Dañado' ELSE c.estado END AS estado,
                e.nombre AS equipo_nombre
            FROM componentes_equipo c
            INNER JOIN equipos e ON c.equipo_id = e.id
            WHERE c.estado <> 'Eliminado'
        ";

        // Si hay un filtro de equipo, agregamos la condición con un AND
        if ($equipo_id) {
            $sql .= " AND c.equipo_id = ? ";
        }

        $sql .= " ORDER BY c.id DESC ";

        $antes = $this->find($id);
        $result = $stmt->execute([
        
        if ($equipo_id) {
            $stmt->execute([$equipo_id]);
        } else {
            $stmt->execute();
        }


        if ($result && $antes) {
            AuditoriaModel::registrar('UPDATE', 'componentes_equipo', (int)$id, $antes, $this->find($id) ?: $data);
        }

        return $result;
        return $stmt->fetchAll();
    }

    // BUSCAR UN COMPONENTE POR ID
    // BUSCAR UN COMPONENTE POR ID
    public function find($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
            $antes = $this->find($id);

                id,
                equipo_id,
                tipo_componente AS tipo,
                marca_modelo,
                capacidad_detalle AS descripcion,

            $result = $stmt->execute([$id]);

            if ($result && $antes) {
                AuditoriaModel::registrar('DELETE', 'componentes_equipo', (int)$id, $antes, ['estado' => 'Eliminado']);
            }

            return $result;
            FROM componentes_equipo
            WHERE id = ?
        ");

        // 🛡️ CORREGIDO: Agregado el signo '$' que faltaba en la variable id
        $stmt->execute([$id]); 
        return $stmt->fetch();
    }

    // CREAR UN NUEVO COMPONENTE
    public function create($data)
    {
        $sql = "
            INSERT INTO componentes_equipo
            (equipo_id, tipo_componente, marca_modelo, capacidad_detalle, estado)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);

        // Homologamos el string 'Dañado' de la vista al enum 'Malo' de la BD
        $estadoBD = ($data['estado'] === 'Dañado') ? 'Malo' : $data['estado'];

        return $stmt->execute([
            $data['equipo_id'],
            $data['tipo'], // 'RAM', 'Disco Duro', etc.
            $data['marca_modelo'] ?? 'Genérico',
            $data['descripcion'], // Mapea a capacidad_detalle
            $estadoBD
        ]);
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
        $estadoBD = ($data['estado'] === 'Dañado') ? 'Malo' : $data['estado'];

        return $stmt->execute([
            $data['equipo_id'],
            $data['tipo'],
            $data['marca_modelo'] ?? 'Genérico',
            $data['descripcion'],
            $estadoBD,
            $id
        ]);
    }

    /**
     * ELIMINAR UN COMPONENTE (BORRADO LÓGICO)
     * 🛡️ MODIFICADO: Actualiza el estado a 'Eliminado' en lugar de destruir la fila
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE componentes_equipo
                SET estado = 'Eliminado'
                WHERE id = ?
            ");

            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * CONTAR TOTAL DE COMPONENTES (CON FILTRO OPCIONAL)
     * 🛡️ MODIFICADO: Ignora los componentes marcados como 'Eliminado'
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