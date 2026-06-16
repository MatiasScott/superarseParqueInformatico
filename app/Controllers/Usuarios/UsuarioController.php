<?php
namespace App\Controllers\Usuarios;

use App\Models\Usuarios\Usuario;

class UsuarioController {

    public function crear() {
        ob_start();
        require_once __DIR__ . '/../../Views/usuarios/crear.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre'   => $_POST['nombre'],
                'email'    => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'password' => $_POST['password'],
                'rol'      => $_POST['rol']
            ];

            $userModel = new Usuario();
            
            if ($userModel->create($data)) {
                // REDIRECCIÓN CORREGIDA PARA XAMPP
                header("Location: /usuarios?success=1");
                exit();
            } else {
                echo "Error al guardar el usuario.";
            }
        }
    }

    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = new \App\Models\Usuarios\Usuario();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['permisos'] = !empty($user['permisos']) ? json_decode($user['permisos'], true) : [];
                $_SESSION['force_password_change'] =
                    isset($user['primer_inicio']) && $user['primer_inicio'] == 1;

                if ($_SESSION['force_password_change']) {
                    header("Location: /cambiar-contrasena");
                    exit();
                }

                header("Location: /dashboard");
                exit();
            } else {
                header("Location: /login?error=1");
                exit();
            }
        }
    }

    public function mostrarCambiarContrasena() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $titulo = 'Cambiar contraseña';
        $activePage = 'cambiar-contrasena';

        ob_start();
        require_once __DIR__ . '/../../Views/usuarios/cambiar_contrasena.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    public function guardarCambiarContrasena() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /cambiar-contrasena");
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        $nuevoPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (trim($nuevoPassword) === '' || $nuevoPassword !== $confirmPassword) {
            header("Location: /cambiar-contrasena?error=1");
            exit();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: /login");
            exit();
        }

        $userModel = new \App\Models\Usuarios\Usuario();
        if ($userModel->changePassword($userId, $nuevoPassword)) {
            $_SESSION['force_password_change'] = false;
            header("Location: /dashboard?password_changed=1");
            exit();
        }

        header("Location: /cambiar-contrasena?error=1");
        exit();
    }
}