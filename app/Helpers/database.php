<?php
namespace App\Helpers;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                $host = getenv('DB_HOST') ?: 'mariadb';
                $db = getenv('DB_NAME') ?: 'superar1_parque_informatico';
                $user = getenv('DB_USER') ?: 'root';
                $pass = getenv('DB_PASS') ?: '';
                $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

                $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

                self::$instance = new \PDO($dsn, $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]);

            } catch (\PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
