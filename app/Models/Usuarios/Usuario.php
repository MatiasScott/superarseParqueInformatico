<?php
// 1. Definir el namespace del modelo
namespace App\Models\Usuarios;

// 2. Importar la clase Database desde su namespace
use App\Helpers\database;

class Usuario {
    private $db;

    public function __construct() {
        // Ahora PHP ya sabe que Database se refiere a App\Helpers\Database
        $this->db = Database::getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? AND estado = 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $columns = 'nombre, email, password_hash, rol';
        $values = '?, ?, ?, ?';
        $params = [
            $data['nombre'],
            $data['email'],
            $passwordHash,
            $data['rol']
        ];

        if ($this->hasPrimerInicioColumn()) {
            $columns .= ', primer_inicio';
            $values .= ', 1';
        }

        $stmt = $this->db->prepare("INSERT INTO usuarios ({$columns}) VALUES ({$values})");
        return $stmt->execute($params);
    }

    private function hasPrimerInicioColumn(): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM usuarios LIKE 'primer_inicio'");
            $stmt->execute();
            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changePassword(int $id, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET password_hash = ?";

        if ($this->hasPrimerInicioColumn()) {
            $sql .= ", primer_inicio = 0";
        }

        $sql .= " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$passwordHash, $id]);
    }
}