<?php

namespace App\Models\Mantenimientos;

use App\Helpers\Database;
use PDO;
// IMPORTAMOS EL MODELO DE AUDITORÍA GLOBAL
use App\Models\Auditoria\AuditoriaModel;

class MantenimientoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // LISTAR TODOS LOS MANTENIMIENTOS
    public function getAll() {
        $sql = "SELECT m.*, e.nombre as equipo_nombre, e.serie as equipo_serie 
                FROM mantenimientos m
                JOIN equipos e ON m.equipo_id = e.id 
                ORDER BY m.fecha_ingreso DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ENCONTRAR UN MANTENIMIENTO POR ID
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM mantenimientos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CREAR ORDEN DE MANTENIMIENTO
    public function create($data) {
        $sql = "INSERT INTO mantenimientos 
                (equipo_id, tipo, descripcion_falla, tecnico_responsable, fecha_ingreso, fecha_salida, estado, tareas_realizadas, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        // Controlamos opcionales para evitar conflictos con valores nulos o vacíos
        $fecha_salida = !empty($data['fecha_salida']) ? $data['fecha_salida'] : null;
        $tareas_realizadas = !empty($data['tareas_realizadas']) ? $data['tareas_realizadas'] : null;
        $observaciones = !empty($data['observaciones']) ? $data['observaciones'] : null;

        $result = $stmt->execute([
            $data['equipo_id'], 
            $data['tipo'], // 'Preventivo' o 'Correctivo'
            $data['descripcion_falla'], 
            $data['tecnico_responsable'], 
            $data['fecha_ingreso'], 
            $fecha_salida, 
            $data['estado'] ?? 'Pendiente', 
            $tareas_realizadas, 
            $observaciones
        ]);

        if ($result) {
            $idNuevoMantenimiento = $this->db->lastInsertId();
            
            // 🛡️ CORRECCIÓN AUDITORÍA: Se cambió 'CREAR' por 'INSERT' según el ENUM de la BD
            AuditoriaModel::registrar('INSERT', 'mantenimientos', $idNuevoMantenimiento, null, $data);
        }

        return $result;
    }

    // ACTUALIZAR MANTENIMIENTO
    public function update($id, $data) {
        // 1. 🔍 CAPTURAR EL ESTADO ANTES DEL CAMBIO
        $mantenimientoAntes = $this->find($id);

        $sql = "UPDATE mantenimientos SET 
                equipo_id = ?, 
                tipo = ?, 
                descripcion_falla = ?, 
                tecnico_responsable = ?, 
                fecha_ingreso = ?, 
                fecha_salida = ?, 
                estado = ?, 
                tareas_realizadas = ?, 
                observaciones = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        $fecha_salida = !empty($data['fecha_salida']) ? $data['fecha_salida'] : null;
        $tareas_realizadas = !empty($data['tareas_realizadas']) ? $data['tareas_realizadas'] : null;
        $observaciones = !empty($data['observaciones']) ? $data['observaciones'] : null;

        $result = $stmt->execute([
            $data['equipo_id'], 
            $data['tipo'], 
            $data['descripcion_falla'], 
            $data['tecnico_responsable'], 
            $data['fecha_ingreso'], 
            $fecha_salida, 
            $data['estado'], 
            $tareas_realizadas, 
            $observaciones, 
            $id
        ]);

        if ($result) {
            // Limpieza de campos innecesarios antes de guardar la auditoría anterior
            if (isset($mantenimientoAntes['created_at'])) unset($mantenimientoAntes['created_at']);

            // 🛡️ CORRECCIÓN AUDITORÍA: Se cambió 'MODIFICAR' por 'UPDATE' según el ENUM de la BD
            AuditoriaModel::registrar('UPDATE', 'mantenimientos', $id, $mantenimientoAntes, $data);
        }

        return $result;
    }

    // ELIMINAR MANTENIMIENTO
    public function delete($id) {
        // 1. 🔍 CAPTURAR DATOS ANTES DE LA ELIMINACIÓN
        $mantenimientoAntes = $this->find($id);

        $stmt = $this->db->prepare("DELETE FROM mantenimientos WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result && $mantenimientoAntes) {
            // 🛡️ CORRECCIÓN AUDITORÍA: Se cambió 'ELIMINAR' por 'DELETE' según el ENUM de la BD
            AuditoriaModel::registrar('DELETE', 'mantenimientos', $id, $mantenimientoAntes, null);
        }

        return $result;
    }
}