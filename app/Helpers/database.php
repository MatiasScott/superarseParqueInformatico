<?php
namespace App\Helpers;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                $host = "localhost";
                $db   = "superar1_parque_informatico";
                $user = "root";
                $pass = "Superarse.2025";
                $charset = 'utf8mb4';

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
