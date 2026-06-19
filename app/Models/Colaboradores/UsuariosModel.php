<?php

namespace App\Models\Colaboradores; // 📁 Ajustado a tu estructura física actual

use App\Helpers\Database;
use App\Models\Auditoria\AuditoriaModel;
use PDO;
use Exception;

class UsuariosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los usuarios ordenados por el más reciente
     */
    public function getAll(): array {
        try {
            $stmt = $this->db->query("SELECT id, nombre, email, rol, estado, primer_inicio, permisos, created_at, updated_at FROM usuarios ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Cuenta el total de usuarios registrados
     */
    public function count(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Crea un nuevo usuario en el sistema con encriptación BCRYPT
     */
    public function create(array $data): bool {
        try {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $rol = $data['rol'] ?? 'usuario';
            $estado = isset($data['estado']) ? (int)$data['estado'] : 1;

            $columns = 'nombre, email, password_hash, rol, estado, created_at, updated_at';
            $placeholders = '?, ?, ?, ?, ?, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP()';
            $params = [
                $data['nombre'],
                $data['email'],
                $passwordHash,
                $rol,
                $estado
            ];

            if ($this->hasPrimerInicioColumn()) {
                $columns .= ', primer_inicio';
                $placeholders .= ', ?';
                $params[] = isset($data['primer_inicio']) ? (int)$data['primer_inicio'] : 1;
            }

            if ($this->hasPermisosColumn()) {
                $columns .= ', permisos';
                $placeholders .= ', ?';
                $params[] = isset($data['permisos']) ? json_encode($data['permisos']) : json_encode(new \stdClass());
            }

            $sql = "INSERT INTO usuarios ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $nuevoId = (int)$this->db->lastInsertId();
                AuditoriaModel::registrar('INSERT', 'usuarios', $nuevoId, null, $this->find($nuevoId) ?: $data);
            }

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verifica si la columna primer_inicio existe en la tabla usuarios
     */
    private function hasPrimerInicioColumn(): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM usuarios LIKE 'primer_inicio'");
            $stmt->execute();
            return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    private function hasPermisosColumn(): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM usuarios LIKE 'permisos'");
            $stmt->execute();
            return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    private function ensurePermisosColumn(): bool
    {
        if ($this->hasPermisosColumn()) {
            return true;
        }

        try {
            $this->db->exec("ALTER TABLE usuarios ADD COLUMN permisos TEXT NULL");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updatePermissions(int $id, array $permisos): bool
    {
        try {
            if (!$this->ensurePermisosColumn()) {
                return false;
            }

            $antes = $this->find($id);
            $json = json_encode($permisos);
            $stmt = $this->db->prepare("UPDATE usuarios SET permisos = ?, updated_at = CURRENT_TIMESTAMP() WHERE id = ?");
            $result = $stmt->execute([$json, $id]);

            if ($result && $antes) {
                AuditoriaModel::registrar('UPDATE', 'usuarios', $id, $antes, $this->find($id) ?: ['permisos' => $permisos]);
            }

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Busca un usuario específico por su ID primario
     */
    public function find(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, email, rol, estado, primer_inicio, permisos, created_at, updated_at FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Busca un usuario por email exclusivamente para procesos de Login
     */
    public function findByEmail(string $email): ?array {
        try {
            // Extrae el password_hash solo si el usuario está activo (estado = 1)
            $stmt = $this->db->prepare("SELECT id, nombre, email, password_hash, rol, estado, primer_inicio, permisos FROM usuarios WHERE email = ? AND estado = 1");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Actualiza los datos del usuario manejando de forma segura la contraseña y el estado funcional
     */
    public function update(int $id, array $data): bool {
        try {
            $antes = $this->find($id);
            $estado = isset($data['estado']) ? (int)$data['estado'] : 1;
            $rol = $data['rol'] ?? 'usuario';
            $primerInicio = isset($data['primer_inicio']) ? (int)$data['primer_inicio'] : 0;

            $permisosJson = null;
            if (isset($data['permisos'])) {
                $permisosJson = json_encode($data['permisos']);
            }

            if (!empty($data['password'])) {
                // Si el operador digitó una nueva clave, se re-encripta
                $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios 
                        SET nombre = ?, email = ?, password_hash = ?, rol = ?, estado = ?, updated_at = CURRENT_TIMESTAMP()";

                if ($this->hasPrimerInicioColumn()) {
                    $sql .= ", primer_inicio = ?";
                }
                if ($this->hasPermisosColumn() && $permisosJson !== null) {
                    $sql .= ", permisos = ?";
                }

                $sql .= " WHERE id = ?";
                $params = [$data['nombre'], $data['email'], $passwordHash, $rol, $estado];
                if ($this->hasPrimerInicioColumn()) {
                    $params[] = $primerInicio;
                }
                if ($this->hasPermisosColumn() && $permisosJson !== null) {
                    $params[] = $permisosJson;
                }
                $params[] = $id;

                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute($params);

                if ($result && $antes) {
                    AuditoriaModel::registrar('UPDATE', 'usuarios', $id, $antes, $this->find($id) ?: $data);
                }

                return $result;
            } else {
                // Si la clave se dejó en blanco, se preserva la actual intacta
                $sql = "UPDATE usuarios 
                        SET nombre = ?, email = ?, rol = ?, estado = ?, updated_at = CURRENT_TIMESTAMP()";

                if ($this->hasPrimerInicioColumn()) {
                    $sql .= ", primer_inicio = ?";
                }
                if ($this->hasPermisosColumn() && $permisosJson !== null) {
                    $sql .= ", permisos = ?";
                }

                $sql .= " WHERE id = ?";
                $params = [$data['nombre'], $data['email'], $rol, $estado];
                if ($this->hasPrimerInicioColumn()) {
                    $params[] = $primerInicio;
                }
                if ($this->hasPermisosColumn() && $permisosJson !== null) {
                    $params[] = $permisosJson;
                }
                $params[] = $id;

                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute($params);

                if ($result && $antes) {
                    AuditoriaModel::registrar('UPDATE', 'usuarios', $id, $antes, $this->find($id) ?: $data);
                }

                return $result;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Elimina un usuario físicamente de la base de datos
     */
    public function delete(int $id): bool {
        try {
            $antes = $this->find($id);
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result && $antes) {
                AuditoriaModel::registrar('DELETE', 'usuarios', $id, $antes, null);
            }

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
}