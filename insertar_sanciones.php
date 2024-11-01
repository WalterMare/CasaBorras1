<?php 



function InsertarSancion($vConexion){
  
    $fechainicio=$_POST['fecha'];
    $dias=$_POST['dias'];
$fechainicial=new DateTime($fechainicio);
$fecha_final=$fechainicial->modify("+$dias days");
$fecha_finalicima= $fecha_final->format('Y-m-d');



    $SQL_Insert="INSERT INTO sancion (idsancion, fecha_inicio, IdTipoSancion, descripcion, idEmpleado, cantidadDias, idEstadoSancion,fecha_fin) 
    VALUES (null,'".$_POST['fecha']."' , '".$_POST['tipo']."' , '".$_POST['descripcion']."' , '".$_POST['empleado']."', '".$_POST['dias']."', '".$_POST['estado']."', '".$fecha_finalicima."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }else{
        if($_POST['tipo']>=3 || $_POST['tipo']<=5){
            Modificar_Estado_Empleado($_POST['empleado'], 0, $vConexion);
        }
    }

    return true;

}
?>

<?php
function Modificar_Estado_Empleado($Id, $Estado, $conexion){
    $SQL="UPDATE empleado AS E SET E.estado=$Estado WHERE E.idempleado=$Id ";

    if(!mysqli_query($conexion,$SQL)){
        return false;
    }
    return true;
}
?>





