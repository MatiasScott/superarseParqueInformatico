<?php

namespace App\Models\Documentos;

use App\Helpers\Database;
use PDO;
use Exception;
use App\Models\Auditoria\AuditoriaModel;

class DocumentosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * 📋 LISTAR TODOS LOS DOCUMENTOS REGISTRADOS
     */
    public function getAll() {
        // Consultamos directo a tu tabla documentos_adjuntos
        $sql = "SELECT d.id, 
                       d.tipo_entidad, 
                       d.entidad_id, 
                       d.tipo_documento, 
                       d.nombre_archivo, 
                       d.ruta_almacenamiento, 
                       d.fecha_subida,
                       u.nombre AS usuario_creador
                FROM documentos_adjuntos d
                LEFT JOIN usuarios u ON d.usuario_id = u.id
                ORDER BY d.fecha_subida DESC, d.id DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🔍 BUSCAR UN DOCUMENTO ESPECÍFICO POR ID
     */
    public function find($id) {
        $sql = "SELECT d.*, u.nombre AS usuario_creador
                FROM documentos_adjuntos d
                LEFT JOIN usuarios u ON d.usuario_id = u.id
                WHERE d.id = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ➕ REGISTRAR UN NUEVO ARCHIVO ADJUNTO (CON AUDITORÍA)
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO documentos_adjuntos 
                        (tipo_entidad, entidad_id, tipo_documento, nombre_archivo, ruta_almacenamiento, usuario_id) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['tipo_entidad'],        // 'Equipo', 'Acta' o 'Mantenimiento'
                $data['entidad_id'],          // El ID de la entidad relacionada
                $data['tipo_documento'],      // 'Acta Entrega', 'Informe Técnico', etc.
                $data['nombre_archivo'],      // Título descriptivo o nombre original
                $data['ruta_almacenamiento'], // Ruta física (uploads/documentos/...)
                $data['usuario_id'] ?? $_SESSION['usuario_id'] ?? 3
            ]);

            if ($result) {
                $idNuevo = $this->db->lastInsertId();
                AuditoriaModel::registrar('INSERT', 'documentos_adjuntos', $idNuevo, null, $data);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error en DocumentosModel::create -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📝 ACTUALIZAR METADATOS DEL DOCUMENTO
     */
    public function update($id, $data) {
        try {
            $documentoAntes = $this->find($id);
            if (!$documentoAntes) return false;

            $sql = "UPDATE documentos_adjuntos SET 
                        nombre_archivo = ?, 
                        tipo_documento = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['nombre_archivo'],
                $data['tipo_documento'],
                $id
            ]);

            if ($result) {
                AuditoriaModel::registrar('UPDATE', 'documentos_adjuntos', $id, $documentoAntes, $data);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error en DocumentosModel::update -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🗑️ ELIMINAR REGISTRO LÓGICO
     */
    public function delete($id) {
        try {
            $documentoAntes = $this->find($id);
            if (!$documentoAntes) return false;

            $stmt = $this->db->prepare("DELETE FROM documentos_adjuntos WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                AuditoriaModel::registrar('DELETE', 'documentos_adjuntos', $id, $documentoAntes, null);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error en DocumentosModel::delete -> " . $e->getMessage());
            return false;
        }
    }
}