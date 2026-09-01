<?php

namespace App\Models\Componentes;

use App\Helpers\Database;
use App\Models\Auditoria\AuditoriaModel;
use Exception;
use PDOException;

class ComponenteModel {

    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * 📋 LISTAR TODOS LOS COMPONENTES (CON FILTRO OPCIONAL)
     * Excluye componentes con borrado lógico (estado = 'Eliminado').
     */
    public function getAll($equipo_id = null)
    {
        try {
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
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🔍 BUSCAR UN COMPONENTE POR ID
     * Retorna los alias exactos mapeados para la Vista ('tipo', 'descripcion')
     */
    public function find($id)
    {
        try {
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
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * ➕ CREAR UN NUEVO COMPONENTE
     * Registra automáticamente en la tabla de auditorías tras el éxito.
     */
    public function create($data)
    {
        try {
            $sql = "
                INSERT INTO componentes_equipo
                (equipo_id, tipo_componente, marca_modelo, capacidad_detalle, estado)
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmt = $this->db->prepare($sql);
            
            // Homologación segura de los estados 'Dañado' / 'Danado' al ENUM 'Malo'
            $estadoInput = isset($data['estado']) ? trim($data['estado']) : '';
            if (strcasecmp($estadoInput, 'Danado') === 0 || 
                strcasecmp($estadoInput, 'Dañado') === 0 || 
                mb_strtolower($estadoInput, 'UTF-8') === 'dañado') {
                $estadoBD = 'Malo';
            } else {
                $estadoBD = !empty($estadoInput) ? $estadoInput : 'Bueno';
            }

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
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 📝 ACTUALIZAR UN COMPONENTE EXISTENTE
     * Blindado contra fallos de codificación de caracteres especiales (eñes/tildes)
     */
    public function update($id, $data)
    {
        try {
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
            
            // 🛡️ Estandarización de entrada inmune a problemas de codificación (Solución al Da単ado)
            $estadoInput = isset($data['estado']) ? trim($data['estado']) : '';
            if (strcasecmp($estadoInput, 'Danado') === 0 || 
                strcasecmp($estadoInput, 'Dañado') === 0 || 
                mb_strtolower($estadoInput, 'UTF-8') === 'dañado') {
                $estadoBD = 'Malo';
            } else {
                $estadoBD = !empty($estadoInput) ? $estadoInput : 'Bueno';
            }

            // Captura el estado previo antes del cambio para la auditoría
            $antes = $this->find($id);

            $result = $stmt->execute([
                $data['equipo_id'],
                $data['tipo'],
                $data['marca_modelo'] ?? 'Generico',
                $data['descripcion'],
                $estadoBD,
                $id
            ]);

            // Si la consulta fue exitosa y existía registro previo, guardamos el log histórico
            if ($result && $antes) {
                AuditoriaModel::registrar('UPDATE', 'componentes_equipo', (int)$id, $antes, $this->find($id) ?: $data);
            }

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 🗑️ ELIMINAR UN COMPONENTE (BORRADO LÓGICO)
     * Cambia el estado a 'Eliminado' y genera el rastro de auditoría correspondiente.
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
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 🔢 CONTAR TOTAL DE COMPONENTES ACTIVOS (CON FILTRO OPCIONAL)
     * Ignora las filas marcadas con borrado lógico.
     */
    public function count($equipo_id = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM componentes_equipo WHERE estado <> 'Eliminado'";

            if ($equipo_id) {
                $sql .= " AND equipo_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$equipo_id]);
            } else {
                $stmt = $this->db->query($sql);
            }

            $result = $stmt->fetch();
            return $result ? (int)$result['total'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}