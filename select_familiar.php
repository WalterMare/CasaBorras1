<?php
function Listar_familiares($vConexion, $persona) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT familiar.*, relacion.* FROM familiar, relacion
    WHERE familiar.IdEmpleado= $persona 
    AND familiar.Idrelacion=relacion.idrelacion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idfamiliar'];
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO']=$data['apellido'];
            $Listado[$i]['DNI']=$data['dni'];
            $Listado[$i]['RELACION']=$data['tipo'];
            $Listado[$i]['IDRELACION']=$data['Idrelacion'];
            $Listado[$i]['FECHANACIMIENTO']=$data['fechaNacimiento'];
            $Listado[$i]['TEL']=$data['tel'];
            $Listado[$i]['IDEMPLEADO']=$data['IdEmpleado'];

            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>


<?php
function Eliminar_Familiar($vConexion , $vIdConsulta) {
    //voy a permitir eliminar si :

    
        $SQL_MiConsulta="SELECT * FROM familiar 
                        WHERE idfamiliar= $vIdConsulta";
    
    
    $rs = mysqli_query($vConexion, $SQL_MiConsulta);
        
    $data = mysqli_fetch_array($rs);

    if (!empty($data['idfamiliar']) ) {
        //si se cumple todo, entonces elimino:
        mysqli_query($vConexion, "DELETE FROM familiar WHERE idfamiliar = $vIdConsulta");
        return true;

    }else {
        return false;
    }
    
}
?>

<?php
function ModificarFamiliar($vConexion, $persona,$empleado)
{

   
$nombre=$_POST["nombre"];
$apellido=$_POST["apellido"];
$fechaNacimiento=$_POST["fechanacimiento"];
$tel=$_POST["tel"];
$idrelacion=$_POST["relacion"];
$dni= $_POST["dni"];

    $SQL_Insert = "UPDATE  familiar
    SET nombre='$nombre', apellido='$apellido', dni='$dni',Idrelacion='$idrelacion', fechaNacimiento='$fechaNacimiento',
    tel='$tel',IdEmpleado='$empleado'
      
    WHERE idfamiliar=$persona";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>

<?php
function Ver_familiar($vConexion, $familiar) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT familiar.*,relacion.*
    FROM familiar, relacion 
    WHERE familiar.idfamiliar= $familiar AND familiar.Idrelacion=relacion.idrelacion ";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    
     $data = mysqli_fetch_array($rs);
    if(!empty($data )) {
            $Listado['ID']=$data['idfamiliar'];
            $Listado['NOMBRE']=$data['nombre'];
            $Listado['APELLIDO']=$data['apellido'];
            $Listado['DOCUMENTO']=$data['dni'];
            $Listado['IDRELACION']=$data['Idrelacion'];
            $Listado['RELACION']=$data['tipo'];
            $Listado['FECHANACIMIENTO']=$data['fechaNacimiento'];
            $Listado['TEL']=$data['tel'];
            $Listado['IDEMPLEADO']=$data['IdEmpleado'];

            
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>
