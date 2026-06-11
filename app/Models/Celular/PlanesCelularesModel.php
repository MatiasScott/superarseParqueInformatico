<?php

namespace App\Models\Celular;

use App\Helpers\Database;
use PDO;
use PDOException;
use Exception;

class PlanesCelularesModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtener el inventario global de telefonía y terminales
     */
 /**
     * Obtener el inventario global de telefonía con soporte para búsquedas dinámicas
     */
    public function getAll($search = '') {
        try {
            if (!empty($search)) {
                // Si hay una palabra clave, buscamos coincidencias parciales
                $sql = "SELECT * FROM planes_celulares 
                        WHERE numero_celular LIKE :search 
                           OR celular_marca LIKE :search 
                           OR celular_modelo LIKE :search 
                           OR celular_imei_1 LIKE :search 
                        ORDER BY created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':search' => '%' . $search . '%']);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Si no hay búsqueda, se comporta exactamente igual que antes
            $sql = "SELECT * FROM planes_celulares ORDER BY created_at DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en PlanesCelularesModel::getAll -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar una ficha móvil específica por su ID único
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM planes_celulares WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PlanesCelularesModel::getById -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar un nuevo activo celular con su plan
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO planes_celulares (
                        numero_celular, operador, nombre_plan, costo_mensual, tipo_sim,
                        celular_marca, celular_modelo, celular_imei_1, celular_imei_2,
                        celular_serie, celular_color, celular_almacenamiento, celular_ram,
                        bateria_salud, estado_plan, observacion
                    ) VALUES (
                        :numero_celular, :operador, :nombre_plan, :costo_mensual, :tipo_sim,
                        :celular_marca, :celular_modelo, :celular_imei_1, :celular_imei_2,
                        :celular_serie, :celular_color, :celular_almacenamiento, :celular_ram,
                        :bateria_salud, :estado_plan, :observacion
                    )";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':numero_celular'         => $data['numero_celular'],
                ':operador'               => $data['operador'],
                ':nombre_plan'            => $data['nombre_plan'],
                ':costo_mensual'          => !empty($data['costo_mensual']) ? $data['costo_mensual'] : 0.00,
                ':tipo_sim'               => $data['tipo_sim'],
                ':celular_marca'          => $data['celular_marca'],
                ':celular_modelo'         => $data['celular_modelo'],
                ':celular_imei_1'         => $data['celular_imei_1'],
                ':celular_imei_2'         => !empty($data['celular_imei_2']) ? $data['celular_imei_2'] : 'N/A',
                ':celular_serie'          => !empty($data['celular_serie']) ? $data['celular_serie'] : 'N/D',
                ':celular_color'          => !empty($data['celular_color']) ? $data['celular_color'] : 'N/D',
                ':celular_almacenamiento' => $data['celular_almacenamiento'],
                ':celular_ram'            => !empty($data['celular_ram']) ? $data['celular_ram'] : 'N/D',
                ':bateria_salud'          => !empty($data['bateria_salud']) ? $data['bateria_salud'] : '100%',
                ':estado_plan'            => !empty($data['estado_plan']) ? $data['estado_plan'] : 'Disponible',
                ':observacion'            => !empty($data['observacion']) ? $data['observacion'] : null
            ]);
        } catch (PDOException $e) {
            error_log("Error en PlanesCelularesModel::create -> " . $e->getMessage());
            throw new Exception("Error al insertar el dispositivo en la base: " . $e->getMessage());
        }
    }

    /**
     * Actualizar los datos técnicos o contractuales de un celular/plan existente
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE planes_celulares SET 
                        numero_celular         = :numero_celular, 
                        operador               = :operador, 
                        nombre_plan            = :nombre_plan, 
                        costo_mensual          = :costo_mensual, 
                        tipo_sim               = :tipo_sim, 
                        celular_marca          = :celular_marca, 
                        celular_modelo         = :celular_modelo, 
                        celular_imei_1         = :celular_imei_1, 
                        celular_imei_2         = :celular_imei_2, 
                        celular_serie          = :celular_serie, 
                        celular_color          = :celular_color, 
                        celular_almacenamiento = :celular_almacenamiento, 
                        celular_ram            = :celular_ram, 
                        bateria_salud          = :bateria_salud, 
                        estado_plan            = :estado_plan, 
                        observacion            = :observacion
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':id'                     => (int)$id,
                ':numero_celular'         => $data['numero_celular'],
                ':operador'               => $data['operador'],
                ':nombre_plan'            => $data['nombre_plan'],
                ':costo_mensual'          => !empty($data['costo_mensual']) ? $data['costo_mensual'] : 0.00,
                ':tipo_sim'               => $data['tipo_sim'],
                ':celular_marca'          => $data['celular_marca'],
                ':celular_modelo'         => $data['celular_modelo'],
                ':celular_imei_1'         => $data['celular_imei_1'],
                ':celular_imei_2'         => !empty($data['celular_imei_2']) ? $data['celular_imei_2'] : 'N/A',
                ':celular_serie'          => !empty($data['celular_serie']) ? $data['celular_serie'] : 'N/D',
                ':celular_color'          => !empty($data['celular_color']) ? $data['celular_color'] : 'N/D',
                ':celular_almacenamiento' => $data['celular_almacenamiento'],
                ':celular_ram'            => !empty($data['celular_ram']) ? $data['celular_ram'] : 'N/D',
                ':bateria_salud'          => !empty($data['bateria_salud']) ? $data['bateria_salud'] : '100%',
                ':estado_plan'            => !empty($data['estado_plan']) ? $data['estado_plan'] : 'Disponible',
                ':observacion'            => !empty($data['observacion']) ? $data['observacion'] : null
            ]);
        } catch (PDOException $e) {
            error_log("Error en PlanesCelularesModel::update -> " . $e->getMessage());
            throw new Exception("Error al actualizar el registro en la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Eliminar físicamente del inventario
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM planes_celulares WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error en PlanesCelularesModel::delete -> " . $e->getMessage());
            throw new Exception("El activo celular está vinculado a un historial.");
        }
    }
}