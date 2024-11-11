<?php 
function DatosLogin($vUsuario, $vClave, $vConexion){
    $Usuario=array();
    //agrego la función de MD5 para que se encripte y compare con lo de la tabla
    $SQL="SELECT U.idusuario, U.user, U.clave,U.IdEmpleado, U.Idtipo, E.idempleado, E.nombre,E.apellido, E.estado, E.imagen, T.idtipo, T.descripcion
     FROM usuario U, empleado E, tipo T
     WHERE U.user='$vUsuario' AND U.clave = '$vClave' 
     AND U.IdEmpleado = E.idempleado AND U.Idtipo = T.idtipo ";

    $rs = mysqli_query($vConexion, $SQL);
        
    $data = mysqli_fetch_array($rs) ;
    if (!empty($data)) {
        $Usuario['NOMBRE'] = $data['nombre'];
        $Usuario['APELLIDO'] = $data['apellido'];
        $Usuario['TIPO'] = $data['Idtipo'];
        $Usuario['ESTADO']=$data['estado'];
        
        
       
       if (empty( $data['imagen'])) {
            $data['imagen'] = "profile.jpg"; 
        }
        $Usuario['IMG'] = $data['imagen'];
       
        //agregados
        $Usuario['ID'] = $data['idusuario'];
        $Usuario['NOMBRE_TIPO'] = $data['descripcion'];
        
    }
    return $Usuario;
}

?>