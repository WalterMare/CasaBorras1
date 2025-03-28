<?php
function Validar_Datos() {
    $vMensaje = '';

    // Campos obligatorios simplificados
    $camposObligatorios = ['estado', 'empleado', 'fecha_inicio', 'fecha_fin', 'año', 'cantidad_dias'];
    foreach ($camposObligatorios as $campo) {
        if (empty($_POST[$campo])) {
            $vMensaje .= "Complete el campo requerido: " . ucfirst(str_replace('_', ' ', $campo)) . "<br>";
        }
    }

    // Validación de fechas
    if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
        try {
            $fechaInicio = new DateTime($_POST['fecha_inicio']);
            $fechaFin = new DateTime($_POST['fecha_fin']);
            
            $mesInicio = $fechaInicio->format('m');
            $mesFin = $fechaFin->format('m');
            
            if ($mesInicio < 10 && $mesInicio > 4) {
                $vMensaje .= "Período vacacional no válido (octubre-abril)<br>";
            }
            
            if ($mesFin > 4) {
                $vMensaje .= "Las vacaciones deben finalizar antes de mayo<br>";
            }
            
            if ($fechaFin < $fechaInicio) {
                $vMensaje .= "Fecha final anterior a la inicial<br>";
            }
            
            // Validación días calculados
            $diasCalculados = (int)(($fechaFin->getTimestamp() - $fechaInicio->getTimestamp()) / (60 * 60 * 24)) + 1;
            if ($_POST['cantidad_dias'] != $diasCalculados) {
                $vMensaje .= "Inconsistencia en días solicitados<br>";
            }
            
        } catch (Exception $e) {
            $vMensaje .= "Error en formato de fechas<br>";
        }
    }

    // Validación días disponibles
    if (!empty($_POST['dias']) && !empty($_POST['cantidad_dias'])) {
        $diasDisponibles = (int)$_POST['dias'];
        $diasSolicitados = (int)$_POST['cantidad_dias'];
        
        if ($diasSolicitados > $diasDisponibles) {
            $vMensaje .= "Días solicitados exceden los disponibles<br>";
        }
        
        if (empty($_POST['vacaciones_restantes']) && $_POST['vacaciones_restantes'] !== '0') {
            $vMensaje .= "Error en cálculo de días restantes<br>";
        }
    }

    // Limpieza básica de datos
    array_walk($_POST, function(&$valor) {
        $valor = trim(htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'));
    });

    return $vMensaje;
}


