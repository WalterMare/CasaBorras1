<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$mensaje='';
try {
    $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Comprobar si POST está vacío
    if (empty($_POST)) {
        throw new Exception("No se recibieron datos del formulario.");
    }

    // Validar campos obligatorios
    $camposRequeridos = ['fechainicio', 'fechafin', 'IdTipo', 'idEmpleado', 'IdEstado'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es obligatorio.");
        }
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // Insertar licencia
    $stmt = $pdo->prepare("INSERT INTO licencia (fechainicio, fechafin, IdTipo, idEmpleado, IdEstado, cantidaddias) 
                           VALUES (:fechainicio, :fechafin, :IdTipo, :idEmpleado, :IdEstado, DATEDIFF(:fechafin, :fechainicio))");
    $stmt->execute([
        ':fechainicio' => $_POST['fechainicio'],
        ':fechafin' => $_POST['fechafin'],
        ':IdTipo' => $_POST['IdTipo'],
        ':idEmpleado' => $_POST['idEmpleado'],
        ':IdEstado' => $_POST['IdEstado']
    ]);

    $idLicencia = $pdo->lastInsertId();
   
    // Actualizar estado del empleado a 0 (inactivo)
    $stmt = $pdo->prepare("UPDATE empleado SET estado = 0 WHERE idempleado = :idEmpleado");
    $stmt->execute([':idEmpleado' => $_POST['idEmpleado']]);
    
    if (!empty($_POST['detalles_descripcion'])) {
      foreach ($_POST['detalles_descripcion'] as $index => $descripcion) {
          $documentacion = null;
  
          // Manejo correcto de archivos subidos
          if (!empty($_FILES['detalles_documentacion']['tmp_name'][$index]) && is_uploaded_file($_FILES['detalles_documentacion']['tmp_name'][$index])) {
              $documentacion = file_get_contents($_FILES['detalles_documentacion']['tmp_name'][$index]);
          }
  
          $stmt = $pdo->prepare("INSERT INTO detallelicencia (idLicencia, descripcion, documentacion, FechaCreacion, idUsuario) 
                                 VALUES (:idLicencia, :descripcion, :documentacion, :FechaCreacion, :idUsuario)");
          $stmt->execute([
              ':idLicencia' => $idLicencia,
              ':descripcion' => $descripcion,
              ':documentacion' => $documentacion,
              ':FechaCreacion' => date('Y-m-d'),
              ':idUsuario' => $_POST['usuario']
          ]);
      }
  }

    // Confirmar transacción
    $pdo->commit();
    $mensaje= "Licencia y detalles registrados exitosamente.";
} catch (Exception $e) {
    // Revertir transacción si está activa
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $mensaje= "Error: " . $e->getMessage();
}
echo "<script>alert('$mensaje'); window.location.href='Registro_Licencia.php';</script>";
?>


