<?php
function Listar_Empleado($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $SQL = "SELECT  E.*, C.idcargo,C.descripcion as nomcargo, S.*, ES.*, P.idprovincia, P.nombre as nomprov
    FROM empleado E, cargo C, provincia P, sexo S, estadocivil ES 
    WHERE  E.idempleado=$empleado
    AND E.Idsexo=S.idsexo 
    AND E.idprovincia=P.idprovincia 
    AND E.Idcargo=C.idcargo 
    AND E.IdestadoCivil=ES.idestadocivil 
    ";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $SQL);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro

    $data = mysqli_fetch_array($rs);
    if (!empty($data)) {
        $Listado['NOMBRE'] = $data['nombre'];
        $Listado['APELLIDO'] = $data['apellido'];
        $Listado['ESTADO'] = $data['estado'];
        $Listado['FECHAINICIO'] = $data['fecha_inicio'];
        $Listado['CIUDAD'] = $data['ciudad'];
        $Listado['TEL'] = $data['tel'];
        $Listado['IMAGEN']= $data['imagen'];
        $Listado['CARGO'] = $data['nomcargo'];
        $Listado['IDCARGO'] = $data['idcargo'];
        $Listado['IDCIVIL']=$data['IdestadoCivil'];
        $Listado['PROVINCIA'] = $data['nomprov'];
        $Listado['IDPROV']=$data['idprovincia'];
        $Listado['ID'] = $data['idempleado'];
        $Listado['SEXO'] = $data['tipo'];
        $Listado['IDSEXO']=$data['Idsexo'];
        $Listado['ESTADOCIVIL'] = $data['descripcion'];
        $Listado['EMAIL'] = $data['email'];
        $Listado['FECHANACIMIENTO'] = $data['fechaNacimiento'];
        $Listado['DIRECCION'] = $data['direccion'];
        $Listado['DNI'] = $data['dni'];
    }
    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Modificar_Estado_Usuario($Id, $Estado, $conexion){
    $SQL="UPDATE empleado AS E SET E.estado=$Estado WHERE E.idempleado=$Id ";

    if(!mysqli_query($conexion,$SQL)){
        return false;
    }
    return true;
}
?>
