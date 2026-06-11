<?php
require_once __DIR__ . '/app/Helpers/database.php';
use App\Helpers\Database;
$db = Database::getConnection();

$stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'primer_inicio'");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_dump($columns);

$stmt2 = $db->query("SELECT id,email,nombre,rol,primer_inicio FROM usuarios ORDER BY id DESC LIMIT 5");
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
var_dump($rows);
