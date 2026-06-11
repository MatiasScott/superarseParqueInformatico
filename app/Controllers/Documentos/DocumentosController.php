<?php

namespace App\Controllers\Documentos;

use App\Models\Documentos\DocumentosModel;
use App\Models\Asignaciones\AsignacionesModel;

class DocumentoController {

    /**
     * 📋 PANEL PRINCIPAL DE DOCUMENTOS
     */
    public function index() {
        $model = new DocumentosModel();
        $documentos = $model->getAll();

        // Cargamos las actas de asignación usando la clase unificada para el selector del formulario
        $actas = (new AsignacionesModel())->getAll();

        ob_start();
        require_once __DIR__ . '/../../Views/documentos/index.php';
        $content = ob_get_clean();

        // 💡 CLAVE: Avisamos al layout que pinte de azul la sección "Documentos Adjuntos"
        $activePage = 'documentos';

        require_once __DIR__ . '/../../Views/layouts/main.php';
    }

    /**
     * 💾 PROCESAR, VALIDAR Y GUARDAR DOCUMENTO ADJUNTO
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Validar si el archivo existe en el envío y no presenta anomalías
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                header("Location: /superarseParqueInformatico/public/documentos?msg=error_archivo");
                exit();
            }

            $file = $_FILES['archivo'];
            $fileName = $file['name'];
            $fileTmp = $file['tmp_name'];
            
            // 🔒 RESTRICCIÓN ESTRICTA: Solo extensiones .pdf
            $allowedExtensions = ['pdf'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowedExtensions)) {
                header("Location: /superarseParqueInformatico/public/documentos?msg=extension_no_valida");
                exit();
            }

            // 3. Estructuración del directorio raíz de carga
            $uploadDir = __DIR__ . '/../../public/uploads/documentos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Nombre único aleatorio para prevenir duplicados o sobreescritura accidental
            $newFileName = 'DOC-' . time() . '-' . uniqid() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            // 4. Mover el archivo al almacenamiento físico e insertar el registro en la BD
            if (move_uploaded_file($fileTmp, $destination)) {
                $model = new DocumentosModel();
                $relativePath = 'uploads/documentos/' . $newFileName;

                // Definición estructural de la tabla polimórfica 'documentos_adjuntos'
                $tipoEntidad = 'Acta'; 
                $entidadId = $_POST['acta_id'] ?? 0;

                $data = [
                    'tipo_entidad'        => $tipoEntidad,
                    'entidad_id'          => $entidadId,
                    'tipo_documento'      => $_POST['tipo_documento'], // 'Acta Entrega', 'Informe Técnico', etc.
                    'nombre_archivo'      => trim($_POST['titulo']),
                    'ruta_almacenamiento' => $relativePath,
                    'usuario_id'          => $_SESSION['usuario_id'] ?? 3
                ];

                $result = $model->create($data);
                $msg = $result ? 'guardado' : 'error_bd';
            } else {
                $msg = 'error_subida';
            }

            header("Location: /superarseParqueInformatico/public/documentos?msg=" . $msg);
            exit();
        }
    }

    /**
     * 📝 ACTUALIZAR METADATOS DE UN DOCUMENTO ADJUNTO
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                header("Location: /superarseParqueInformatico/public/documentos");
                exit();
            }

            $model = new DocumentosModel();
            $data = [
                'nombre_archivo' => trim($_POST['titulo']),
                'tipo_documento' => $_POST['tipo_documento']
            ];

            $result = $model->update($id, $data);
            $msg = $result ? 'actualizado' : 'error';
            header("Location: /superarseParqueInformatico/public/documentos?msg=" . $msg);
            exit();
        }
    }

    /**
     * 🗑️ ELIMINAR REGISTRO LÓGICO Y REMOVER ARCHIVO FÍSICO DEL SERVIDOR
     */
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /superarseParqueInformatico/public/documentos");
            exit();
        }

        $model = new DocumentosModel();
        $documento = $model->find($id);

        if ($documento) {
            // Eliminar físicamente el PDF guardado para no dejar archivos huérfanos
            $filePath = __DIR__ . '/../../public/' . $documento['ruta_almacenamiento'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $result = $model->delete($id);
            $msg = $result ? 'eliminado' : 'error';
        } else {
            $msg = 'no_encontrado';
        }

        header("Location: /superarseParqueInformatico/public/documentos?msg=" . $msg);
        exit();
    }
}