<?php
require_once __DIR__ . '/app/Helpers/database.php';
require_once __DIR__ . '/app/Models/Colaboradores/UsuariosModel.php';
require_once __DIR__ . '/app/Models/Usuarios/Usuario.php';

use App\Helpers\Database;
use App\Models\Colaboradores\UsuariosModel;
use App\Models\Usuarios\Usuario;

$db = Database::getConnection();

$uModel = new UsuariosModel();
$email = 'tmp_' . time() . '@superarse.test';
$data = [
    'nombre' => 'tmp test',
    'email' => $email,
    'password' => 'Test1234!',
    'rol' => 'usuario',
    'estado' => 1,
];

if ($uModel->create($data)) {
    echo "Created user via UsuariosModel: $email\n";
    $stmt = $db->prepare('SELECT id, email, primer_inicio FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    var_dump($stmt->fetch(PDO::FETCH_ASSOC));
    $db->prepare('DELETE FROM usuarios WHERE email = ?')->execute([$email]);
} else {
    echo "Failed to create via UsuariosModel\n";
}

$oModel = new Usuario();
$email2 = 'tmp2_' . time() . '@superarse.test';
$data2 = [
    'nombre' => 'tmp2 test',
    'email' => $email2,
    'password' => 'Test1234!',
    'rol' => 'usuario',
];

if ($oModel->create($data2)) {
    echo "Created user via Usuario model: $email2\n";
    $stmt2 = $db->prepare('SELECT id, email, primer_inicio FROM usuarios WHERE email = ?');
    $stmt2->execute([$email2]);
    var_dump($stmt2->fetch(PDO::FETCH_ASSOC));
    $db->prepare('DELETE FROM usuarios WHERE email = ?')->execute([$email2]);
} else {
    echo "Failed to create via Usuario model\n";
}
