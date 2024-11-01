<?php
function edad($fecha)
{

    $dia = date('j');
    $mes = date('n');
    $anio = date('Y');

    $anioNac = substr($fecha, 0, 4);
    $mesNac = substr($fecha, 5, 2);
    $diaNac = substr($fecha, 8, 2);

    if ($mesNac > $mes) {
        $edad = $anio - $anioNac - 1;
    } else if ($mes == $mesNac && $diaNac > $dia) {
        $edad = $anio - $anioNac - 1;
    } else {
        $edad = $anio - $anioNac;
    }
    return $edad;
}

?>