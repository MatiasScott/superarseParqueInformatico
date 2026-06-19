<?php

namespace App\Models\Equipos;

use App\Helpers\Database;
use App\Models\Auditoria\AuditoriaModel;

class Equipo {

    private $db;

    private $estadoIdCache = [];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getEstadoIdByNombre($nombre)
    {
        $key = mb_strtolower(trim((string)$nombre), 'UTF-8');
        if ($key === '') {
            return null;
        }

        if (array_key_exists($key, $this->estadoIdCache)) {
            return $this->estadoIdCache[$key];
        }

        $stmt = $this->db->prepare("SELECT id FROM estados_equipo WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
        $stmt->execute([$nombre]);
        $id = $stmt->fetchColumn();

        $this->estadoIdCache[$key] = $id !== false ? (int)$id : null;
        return $this->estadoIdCache[$key];
    }

    public function getEstadoNombreById($id): ?string
    {
        $idValidado = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idValidado === false) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT nombre FROM estados_equipo WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$idValidado]);
        $nombre = $stmt->fetchColumn();

        return $nombre !== false ? (string)$nombre : null;
    }

    private function normalizarEstadoId($estadoId, $defaultNombre = 'Disponible')
    {
        $id = filter_var($estadoId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id !== false) {
            $stmt = $this->db->prepare("SELECT 1 FROM estados_equipo WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$id]);
            if ($stmt->fetchColumn()) {
                return (int)$id;
            }
        }

        return $this->getEstadoIdByNombre($defaultNombre);
    }

    /**
     * LISTAR TODOS LOS EQUIPOS OPERATIVOS
     * 🛡️ MODIFICADO: Excluye los equipos dados de baja y los borrados lógicamente (estado_id = null o marcado).
     * Para hacerlo infalible, añadimos una bandera o usamos la exclusión del ID de baja y nombres nulos.
     */
    public function getAll()
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                ee.nombre AS estado_nombre,
                s.nombre AS sede_nombre
            FROM equipos e
            LEFT JOIN estados_equipo ee ON e.estado_id = ee.id
            LEFT JOIN sedes s ON e.sede_id = s.id
            WHERE (ee.nombre IS NULL OR LOWER(ee.nombre) <> 'baja')
              AND (ee.nombre IS NULL OR LOWER(ee.nombre) <> 'eliminado')
            ORDER BY e.id DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * HISTORIAL DE EQUIPOS DE BAJA
     * 🚀 INCLUIDO: Muestra el precio final y la última sede donde quedó registrado el activo.
     */
    public function getBajas()
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                ee.nombre AS estado_nombre,
                s.nombre AS sede_nombre
            FROM equipos e
            LEFT JOIN estados_equipo ee ON e.estado_id = ee.id
            LEFT JOIN sedes s ON e.sede_id = s.id
            WHERE LOWER(ee.nombre) = 'baja'
            ORDER BY e.fecha_baja DESC, e.id DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * OBTENER SÓLO LOS EQUIPOS EN BODEGA DISPONIBLES
     */
    public function getDisponibles()
    {
        $sql = "
            SELECT e.*, s.nombre AS sede_nombre 
            FROM equipos e
            INNER JOIN estados_equipo ee ON e.estado_id = ee.id
            LEFT JOIN sedes s ON e.sede_id = s.id
            LEFT JOIN actas_asignacion_detalle aad 
                ON e.id = aad.equipo_id AND aad.fecha_devolucion_equipo IS NULL
            LEFT JOIN actas_asignacion aa 
                ON aad.acta_id = aa.id AND aa.estado_acta = 'Vigente'
            WHERE LOWER(ee.nombre) = 'disponible'
              AND aa.id IS NULL
            ORDER BY e.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * BUSCAR UN EQUIPO POR ID
     * 🛡️ MODIFICADO: Trae el nombre de la sede para el detalle o la vista de "Ver Equipo".
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, s.nombre AS sede_nombre
            FROM equipos e
            LEFT JOIN sedes s ON e.sede_id = s.id
            WHERE e.id = ?
        ");

        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * CREAR UN NUEVO EQUIPO
     * 🚀 MODIFICADO: Inserta campos nativos 'precio' y 'sede_id'.
     */
    public function create($data)
    {
        $estadoId = $this->normalizarEstadoId($data['estado_id'] ?? null, 'Disponible');
        if ($estadoId === null) {
            return false;
        }

        $sql = "
            INSERT INTO equipos
            (
                tipo, nombre, marca, modelo, serie, descripcion, precio, estado_id, sede_id, fecha_baja, motivo_baja
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['tipo'], 
            $data['nombre'], 
            $data['marca'], 
            $data['modelo'], 
            $data['serie'], 
            $data['descripcion'], 
            $data['precio'],    
            $estadoId, 
            $data['sede_id'],   
            $data['fecha_baja'], 
            $data['motivo_baja']
        ]);

        if ($result) {
            $idNuevoEquipo = $this->db->lastInsertId();

            // ⚠️ AUTOMATIZACIÓN: Si ingresa directo a mantenimiento, genera su orden técnica
            $nombreEstado = mb_strtolower((string)($this->getEstadoNombreById($estadoId) ?? ''), 'UTF-8');
            if ($nombreEstado === 'mantenimiento') {
                $this->crearOrdenMantenimientoAutomatica($idNuevoEquipo, $data['descripcion']);
            }

            AuditoriaModel::registrar('INSERT', 'equipos', (int)$idNuevoEquipo, null, $this->find($idNuevoEquipo) ?: $data);
        }

        return $result;
    }

    /**
     * ACTUALIZAR UN EQUIPO EXISTENTE
     * 🚀 MODIFICADO: Permite cambiar el precio y trasladar el equipo de Sede.
     */
    public function update($id, $data)
    {
        $equipoAntes = $this->find($id);
        $estadoActual = isset($equipoAntes['estado_id']) ? (int)$equipoAntes['estado_id'] : null;
        $estadoId = $this->normalizarEstadoId($data['estado_id'] ?? $estadoActual, 'Disponible');
        if ($estadoId === null) {
            return false;
        }

        $sql = "
            UPDATE equipos
            SET
                tipo = ?, nombre = ?, marca = ?, modelo = ?, serie = ?, 
                descripcion = ?, precio = ?, estado_id = ?, sede_id = ?, fecha_baja = ?, motivo_baja = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['tipo'], 
            $data['nombre'], 
            $data['marca'], 
            $data['modelo'], 
            $data['serie'], 
            $data['descripcion'], 
            $data['precio'],    
            $estadoId, 
            $data['sede_id'],   
            $data['fecha_baja'], 
            $data['motivo_baja'], 
            $id
        ]);

        if ($result && $equipoAntes) {
            // ⚠️ AUTOMATIZACIÓN: Si cambia a mantenimiento ahora y antes no lo estaba
            $nombreEstadoNuevo = mb_strtolower((string)($this->getEstadoNombreById($estadoId) ?? ''), 'UTF-8');
            $nombreEstadoAnterior = mb_strtolower((string)($this->getEstadoNombreById($equipoAntes['estado_id'] ?? null) ?? ''), 'UTF-8');
            if ($nombreEstadoNuevo === 'mantenimiento' && $nombreEstadoAnterior !== 'mantenimiento') {
                $this->crearOrdenMantenimientoAutomatica($id, $data['descripcion']);
            }

            AuditoriaModel::registrar('UPDATE', 'equipos', (int)$id, $equipoAntes, $this->find($id) ?: $data);
        }

        return $result;
    }

    /**
     * 🛠️ MÉTODO AUXILIAR PARA GENERAR LA ORDEN TÉCNICA AUTOMÁTICA
     */
    private function crearOrdenMantenimientoAutomatica($equipo_id, $detalles_falla) {
        $sql = "INSERT INTO mantenimientos 
                (equipo_id, tipo, descripcion_falla, tecnico_responsable, fecha_ingreso, estado, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $this->db->prepare($sql);
        
        $tipo = "Correctivo"; 
        $tecnico_responsable = "Por Asignar"; 
        $fecha_ingreso = date('Y-m-d H:i:s');
        $estado = "Pendiente";
        $observaciones = "Orden generada automáticamente desde la actualización del inventario.";

        return $stmt->execute([
            $equipo_id, 
            $tipo, 
            $detalles_falla ?: 'Se requiere revisión en taller.', 
            $tecnico_responsable, 
            $fecha_ingreso, 
            $estado, 
            $observaciones
        ]);
    }

    /**
     * ELIMINAR EQUIPO (BORRADO LÓGICO SEGURO)
     * 🛡️ CORREGIDO: Buscamos si existe un estado 'Eliminado' en tu tabla relacional.
     * Si no existe, desvincula temporalmente el estado asignándole NULL o un ID seguro para ocultarlo.
     */
    public function delete($id)
    {
        $equipoAntes = $this->find($id);

        if ($equipoAntes) {
            try {
                // 1. Archivamos los mantenimientos asociados de forma segura
                $stmtMantenimientos = $this->db->prepare("UPDATE mantenimientos SET estado = 'Archivado' WHERE equipo_id = ?");
                $stmtMantenimientos->execute([$id]);

                // 2. Buscamos dinámicamente si existe el ID del estado "Eliminado" en tu tabla maestra
                $stmtEstado = $this->db->prepare("SELECT id FROM estados_equipo WHERE nombre = 'Eliminado' LIMIT 1");
                $stmtEstado->execute();
                $idEliminado = $stmtEstado->fetchColumn();

                if (!$idEliminado) {
                    // Si el estado 'Eliminado' no existe en tu BD, insertémoslo automáticamente para no romper tus llaves foráneas
                    $insertEstado = $this->db->prepare("INSERT INTO estados_equipo (nombre) VALUES ('Eliminado')");
                    $insertEstado->execute();
                    $idEliminado = $this->db->lastInsertId();
                }

                // 3. APLICAMOS EL BORRADO LÓGICO usando el ID numérico correcto de la Foreign Key
                $stmtEquipo = $this->db->prepare("UPDATE equipos SET estado_id = ? WHERE id = ?");
                $result = $stmtEquipo->execute([$idEliminado, $id]);

                // 4. Registro forense del borrado lógico
                if ($result) {
                    AuditoriaModel::registrar('DELETE', 'equipos', (int)$id, $equipoAntes, ['estado_id' => (int)$idEliminado]);
                }

                return $result;
                
            } catch (\PDOException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * CONTAR EQUIPOS OPERATIVOS
     */
    public function count()
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM equipos e
            LEFT JOIN estados_equipo ee ON e.estado_id = ee.id
            WHERE (ee.nombre IS NULL OR LOWER(ee.nombre) <> 'baja')
              AND (ee.nombre IS NULL OR LOWER(ee.nombre) <> 'eliminado')
        ");

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * LISTAR ESTADOS DISPONIBLES (Excluyendo el estado de borrado lógico)
     */
    public function getEstados()
    {
        $stmt = $this->db->query("SELECT * FROM estados_equipo WHERE nombre <> 'Eliminado' ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    /**
     * OBTENER LISTADO COMPLETO DE SEDES PARA LLENAR LOS SELECTS
     */
    public function getSedes()
    {
        $stmt = $this->db->query("SELECT * FROM sedes ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * ACTUALIZAR ÚNICAMENTE EL ESTADO
     */
    public function updateEstadoSolamente($id, $nuevo_estado_id, $acta_id = null)
    {
        $sql = "UPDATE equipos SET estado_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nuevo_estado_id, $id]);
    }
}