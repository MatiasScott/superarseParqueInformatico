<?php

function sessionHasPermission(string $module, string $action): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        return true;
    }

    $permisos = $_SESSION['permisos'] ?? [];
    return !empty($permisos[$module][$action]);
}
