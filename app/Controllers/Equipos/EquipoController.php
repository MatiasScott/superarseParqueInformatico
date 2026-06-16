<?php

namespace App\Controllers\Equipos;

use App\Models\Equipos\Equipo;

class EquipoController {

    // LISTAR
    public function index()
    {
        $model = new Equipo();

        $equipos = $model->getAll();
        $totalEquipos = $model->count();
        $estados = $model->getEstados();
        $sedes = $model->getSedes(); // 🏢 Añadido para filtros o referencias de sedes en la vista

        // Variable para el layout dinámico
        $activePage = 'equipos';

        ob_start();
        require_once __DIR__ . '/../../Views/equipos/index.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    // FORM CREAR
    public function crear()
    {
        $model = new Equipo();
        $estados = $model->getEstados();
        $sedes = $model->getSedes(); // 🏢 Enviado para pintar el <select> de sedes reales

        // Variable para el layout dinámico
        $activePage = 'equipos';

        ob_start();
        require_once __DIR__ . '/../../Views/equipos/crear.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    // GUARDAR NUEVO EQUIPO
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Equipo();
            $idEstadoDisponible = $model->getEstadoIdByNombre('Disponible') ?? 1;
            $idEstadoBaja = $model->getEstadoIdByNombre('Baja') ?? 5;

            // Si no llega estado_id o llega inválido, el modelo lo normaliza.
            $estado_id = $_POST['estado_id'] ?? $idEstadoDisponible;
            $fecha_baja = null;
            $motivo_baja = null;

            // 🏢 ASIGNACIÓN DE SEDE (Por defecto 1 - Sede Matriz si no envían nada)
            $sede_id = $_POST['sede_id'] ?? 1;

            // 📊 PROCESAMIENTO DE PRECIO (Limpia comas y asegura formato numérico con fallback a 0.00)
            $precio = isset($_POST['precio']) ? floatval(str_replace(',', '', $_POST['precio'])) : 0.00;

            // REVISAR SI ES BAJA
            if ((int)$estado_id === $idEstadoBaja) {
                $fecha_baja = date('Y-m-d');
                $motivo_baja = trim($_POST['descripcion']) ?: 'Retirado por el administrador';
            }

            $data = [
                'tipo'        => trim($_POST['tipo']),
                'nombre'      => trim($_POST['nombre']),
                'marca'       => trim($_POST['marca']),
                'modelo'      => trim($_POST['modelo']),
                'serie'       => trim($_POST['serie']),
                'descripcion' => trim($_POST['descripcion']),
                'precio'      => $precio,    // 💰 Añadido al flujo de persistencia
                'estado_id'   => $estado_id,
                'sede_id'     => $sede_id,  // 🏢 Añadido al flujo de persistencia
                'fecha_baja'  => $fecha_baja,  
                'motivo_baja' => $motivo_baja  
            ];

            try {
                $model->create($data);

                // Éxito: Redirecciona normalmente con un aviso opcional
                header("Location: /equipos?msg=guardado");
                exit();

            } catch (\PDOException $e) {
                // 🔍 CONTROL DE ERRORES: Capturar si la serie ya existe (Error 1062 / Código 23000)
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    header("Location: /equipos?error=duplicado&serie=" . urlencode($data['serie']));
                    exit();
                } else {
                    // Si es otro tipo de error de base de datos, lanzarlo
                    throw $e;
                }
            }
        }
    }

   // EDITAR (Con bloqueo de seguridad)
    public function editar()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: /equipos");
            exit();
        }

        $model = new Equipo();
        $equipo = $model->find($id);
        $idEstadoBaja = $model->getEstadoIdByNombre('Baja') ?? 5;

        // 🛡️ SI EL EQUIPO YA ES BAJA, NO PERMITIR ENTRAR AL FORMULARIO
        if ($equipo && (int)$equipo['estado_id'] === (int)$idEstadoBaja) {
            header("Location: /equipos?error=equipo_de_baja");
            exit();
        }

        $estados = $model->getEstados();
        $sedes = $model->getSedes(); // 🏢 Enviado para pintar el <select> en el formulario de edición
        $activePage = 'equipos';

        ob_start();
        require_once __DIR__ . '/../../Views/equipos/editar.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../../Views/Layouts/main.php';
    }

    // ACTUALIZAR (Con bloqueo de seguridad)
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = $_POST['id'];
            $estado_id = $_POST['estado_id'];
            
            // 🏢 ACTUALIZACIÓN DE SEDE
            $sede_id = $_POST['sede_id'] ?? 1;

            // 📊 PROCESAMIENTO DE PRECIO
            $precio = isset($_POST['precio']) ? floatval(str_replace(',', '', $_POST['precio'])) : 0.00;

            // 🛡️ VERIFICAR EN LA BD QUE NO SE ESTÉ INTENTANDO ALTERAR UN EQUIPO YA DADO DE BAJA
            $model = new Equipo();
            $equipoActual = $model->find($id);
            $idEstadoBaja = $model->getEstadoIdByNombre('Baja') ?? 5;
            if ($equipoActual && (int)$equipoActual['estado_id'] === (int)$idEstadoBaja) {
                header("Location: /equipos?error=modificacion_denegada");
                exit();
            }

            $fecha_baja = null;
            $motivo_baja = null;

            if ((int)$estado_id === $idEstadoBaja) {
                $fecha_baja = date('Y-m-d');
                $motivo_baja = trim($_POST['descripcion']) ?: 'Retirado por el administrador';
            }

            $data = [
                'tipo'        => trim($_POST['tipo']),
                'nombre'      => trim($_POST['nombre']),
                'marca'       => trim($_POST['marca']),
                'modelo'      => trim($_POST['modelo']),
                'serie'       => trim($_POST['serie']),
                'descripcion' => trim($_POST['descripcion']),
                'precio'      => $precio,    // 💰 Añadido a los datos de actualización
                'estado_id'   => $estado_id,
                'sede_id'     => $sede_id,  // 🏢 Añadido a los datos de actualización
                'fecha_baja'  => $fecha_baja,  
                'motivo_baja' => $motivo_baja  
            ];

            try {
                $model->update($id, $data);
                header("Location: /equipos?msg=actualizado");
                exit();

            } catch (\PDOException $e) {
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    header("Location: /equipos?error=duplicado&serie=" . urlencode($data['serie']));
                    exit();
                } else {
                    throw $e;
                }
            }
        }
    }

    // ELIMINAR
    public function eliminar()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $model = new Equipo();
            // 🛡️ El modelo procesará internamente el UPDATE hacia el estado 'Eliminado'
            $model->delete($id);
        }

        header("Location: /equipos?msg=eliminado");
        exit();
    }
}