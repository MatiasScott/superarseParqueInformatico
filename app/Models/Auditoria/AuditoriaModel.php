<?php

namespace App\Models\Auditoria;

use App\Helpers\Database;
use PDO;
use Exception;

class AuditoriaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * 🛡️ NUEVO MÉTODO ESTÁTICO: REGISTRAR UN EVENTO EN LA AUDITORÍA
     * Convierte los datos a JSON automáticamente y detecta la IP del cliente.
     */
    public static function registrar(string $accion, string $tabla, int $registro_id, ?array $anterior = null, ?array $nuevo = null): bool {
        try {
            $db = Database::getConnection();
            
            $sql = "INSERT INTO auditoria_sistema 
                        (accion, tabla_afectada, registro_id, valores_anteriores, valores_nuevos, usuario_id, ip_origen, fecha_evento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Detectamos la IP real del cliente de forma segura
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            }

            $usuarioId = self::resolverUsuarioIdAuditoria();

            $stmt = $db->prepare($sql);
            return $stmt->execute([
                strtoupper($accion),
                $tabla,
                $registro_id,
                $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) : null,
                $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) : null,
                $usuarioId,
                $ip,
                date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Fallo crítico escribiendo auditoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un usuario válido para auditoría sin romper FK.
     */
    private static function resolverUsuarioIdAuditoria(): ?int {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['usuario_id'])) {
            $id = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                return (int)$id;
            }
        }

        // Si no hay sesión activa o no hay usuario válido, registrar como sistema (NULL).
        return null;
    }

    /**
     * Obtiene el historial completo de auditoría del sistema,
     * cruzando con el usuario que ejecutó la acción.
     */
    public function obtenerLogsAuditoria(): array {
        try {
            $sql = "SELECT 
                        a.id,
                        a.accion,
                        a.tabla_afectada,
                        a.registro_id,
                        a.valores_anteriores,
                        a.valores_nuevos,
                        a.ip_origen,
                        a.fecha_evento,
                        u.nombre AS usuario_nombre,
                        u.rol AS usuario_rol
                    FROM auditoria_sistema a
                    LEFT JOIN usuarios u ON a.usuario_id = u.id
                    ORDER BY a.fecha_evento DESC, a.id DESC 
                    LIMIT 500"; 
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene resúmenes estadísticos rápidos sobre los eventos del sistema
     */
    public function obtenerMetricasAuditoria(): array {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_eventos,
                        SUM(CASE WHEN accion = 'INSERT' THEN 1 ELSE 0 END) as total_inserts,
                        SUM(CASE WHEN accion = 'UPDATE' THEN 1 ELSE 0 END) as total_updates,
                        SUM(CASE WHEN accion = 'DELETE' THEN 1 ELSE 0 END) as total_deletes
                    FROM auditoria_sistema";
            
            $result = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $result ?: ['total_eventos' => 0, 'total_inserts' => 0, 'total_updates' => 0, 'total_deletes' => 0];
        } catch (Exception $e) {
            return ['total_eventos' => 0, 'total_inserts' => 0, 'total_updates' => 0, 'total_deletes' => 0];
        }
    }
}