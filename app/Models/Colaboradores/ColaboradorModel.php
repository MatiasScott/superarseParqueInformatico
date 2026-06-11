<?php

namespace App\Models\Colaboradores;

use App\Helpers\Database;
use PDO;
use App\Models\Auditoria\AuditoriaModel;

class ColaboradorModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Listar todos los colaboradores con el nombre de su sede
    public function getAll() {
        // Añadimos el LEFT JOIN para traer el nombre real de la sede
        $sql = "SELECT c.*, s.nombre AS sede_nombre 
                FROM colaboradores c
                LEFT JOIN sedes s ON c.sede_id = s.id 
                ORDER BY c.nombres ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar un colaborador por su ID
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM colaboradores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo colaborador con Auditoría (Incluye sede_id)
    public function create($data) {
        $sql = "INSERT INTO colaboradores (nombres, cargo, correo, area, fecha_ingreso, estado, sede_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['nombres'],
            $data['cargo'] ?? null,
            $data['correo'] ?? null,
            $data['area'] ?? null,
            $data['fecha_ingreso'] ?? null,
            $data['estado'] ?? 1,
            $data['sede_id'] ?? 1 // Se añade el mapeo de la sede (por defecto 1 si viene vacío)
        ]);

        if ($result) {
            $nuevoId = $this->db->lastInsertId();
            AuditoriaModel::registrar('INSERT', 'colaboradores', $nuevoId, null, $data);
        }

        return $result;
    }

    // Actualizar colaborador con Auditoría (Incluye sede_id)
    public function update($id, $data) {
        $colaboradorAntes = $this->find($id);

        $sql = "UPDATE colaboradores SET 
                nombres = ?, 
                cargo = ?, 
                correo = ?, 
                area = ?, 
                fecha_ingreso = ?, 
                estado = ?,
                sede_id = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['nombres'],
            $data['cargo'] ?? null,
            $data['correo'] ?? null,
            $data['area'] ?? null,
            $data['fecha_ingreso'] ?? null,
            $data['estado'],
            $data['sede_id'], // Se añade el mapeo de la sede en la actualización
            $id
        ]);

        if ($result) {
            if (isset($colaboradorAntes['created_at'])) unset($colaboradorAntes['created_at']);
            if (isset($colaboradorAntes['updated_at'])) unset($colaboradorAntes['updated_at']);
            
            AuditoriaModel::registrar('UPDATE', 'colaboradores', $id, $colaboradorAntes, $data);
        }

        return $result;
    }

    // Eliminar colaborador
    public function delete($id) {
        $colaboradorAntes = $this->find($id);

        try {
            $stmt = $this->db->prepare("DELETE FROM colaboradores WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result && $colaboradorAntes) {
                AuditoriaModel::registrar('DELETE', 'colaboradores', $id, $colaboradorAntes, null);
            }
            return $result;
        } catch (\PDOException $e) {
            // Captura si falla por restricción de llave foránea (colaborador con actas)
            return false;
        }
    }

    // ==========================================
    // NUEVO MÉTODO: Obtener sedes directamente
    // ==========================================
    public function getSedes() {
        $sql = "SELECT id, nombre FROM sedes ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}