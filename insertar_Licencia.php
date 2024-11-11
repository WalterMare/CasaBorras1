<?php 



function InsertarLicencia($vConexion){
  
    $fechainicio=$_POST['fecha'];
    $dias=$_POST['dias'];
$fechainicial=new DateTime($fechainicio);
$fecha_final=$fechainicial->modify("+$dias days");
$fecha_finalicima= $fecha_final->format('Y-m-d');



    $SQL_Insert="INSERT INTO licencia (idlicencia, fechainicio, fechafin, IdTipo, idEmpleado, IdEstado, cantidaddias) 
    VALUES (null,'".$_POST['fecha']."', '".$fecha_finalicima."' , '".$_POST['tipo']."' , '".$_POST['empleado']."', '".$_POST['estado']."', '".$_POST['dias']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }else{
        if(!empty($_POST['tipo'])){
            Modificar_EstadoLicencia_Empleado($_POST['empleado'], 0, $vConexion);
        }
    }

    return true;

}
?>

<?php
function Modificar_EstadoLicencia_Empleado($Id, $Estado, $conexion){
    $SQL="UPDATE empleado AS E SET E.estado=$Estado WHERE E.idempleado=$Id ";

    if(!mysqli_query($conexion,$SQL)){
        return false;
    }
    return true;
}
?>