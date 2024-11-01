<?php 
function cal_Fecha ($Fecha_viaje, $Fecha_actual){ 

date_default_timezone_set("America/Argentina/Cordoba");


//de esta manera sabemos cual es la fecha de mañana (sumamos un dia a hoy)
$Maniana= date("d/m/y",strtotime(date('y-m-d')." + 1 days"));   

//con ambos datos, podemos preguntar si la fecha del viaje es mañana?
if ($Fecha_viaje==$Maniana){ 
    echo '"table-warning"  data-bs-original-title="Viaje de mañana "'; 
}

//la fecha del viaje es menor a hoy?
if ($Fecha_viaje > $Fecha_actual){
    echo '"table-success"  data-bs-original-title="Viaje realizado "';
    
   
} else if($Fecha_actual==$Fecha_viaje){
    echo '"table-danger" data-bs-original-title="Viaje de hoy "' ;
}

}?>