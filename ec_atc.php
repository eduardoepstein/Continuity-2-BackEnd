<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
if($id || $id_paciente) {
  $where = $id ? " where id = ? " : ( 
    $id_paciente ? " where idpaciente = ? ": null) ;      
  
  $sql = "
  select
  ec.id,
  ec.idpaciente,
  ec.fecha,
  ec.da,
  ec.danrototal,
  ec.dastent,
  ec.danrostent,
  ec.dastentfarm,
  ec.danrostentfarm,
  ec.cd,
  ec.cdnrototal,
  ec.cdstent,
  ec.cdnrostent,
  ec.cdstentfarm,
  ec.cdnrostentfarm,
  ec.cx,
  ec.cxnrototal,
  ec.cxstent,
  ec.cxnrostent,
  ec.cxstentfarm,
  ec.cxnrostentfarm,
  ec.diag,
  ec.diagnrototal,
  ec.diagstent,
  ec.diagnrostent,
  ec.diagstentfarm,
  ec.diagnrostentfarm,
  ec.fecproceso,
  ec.usrproceso,
      usr.nombre
    from ec_atc ec
    left join usuarios usr on usr.codigo = ec.usrproceso   
    {$where}       
    order by fecha desc
";
$params = $id ? array("i",&$id) : array("i",&$id_paciente);

$mydatos = my_query($sql, $params);  
if ($mydatos !== false) {
  $codigo = 0;
  $descripcion = "OK";
  $datos = array();
   if ($mydatos["datos"]) {    
    $id = -1;    
    foreach ($mydatos["datos"] as $dato) {
      
        $id = $dato["id"];                       
        $atc = array(          
          "id_paciente" =>$dato["idpaciente"],
          "fecha" =>$dato["fecha"],
          "da" =>$dato["da"],
          "danrototal" =>$dato["danrototal"],
          "dastent" =>$dato["dastent"],
          "danrostent" =>$dato["danrostent"],
          "dastentfarm" =>$dato["dastentfarm"],
          "cd" =>$dato["cd"],
          "cdnrototal" =>$dato["cdnrototal"],
          "cdstent" =>$dato["cdstent"],
          "cdnrostent" =>$dato["cdnrostent"],
          "cdstentfarm" =>$dato["cdstentfarm"],
          "cdnrostentfarm" =>$dato["cdnrostentfarm"],
          "cx" =>$dato["cx"],
          "cxnrototal" =>$dato["cxnrototal"],
          "cxstent" =>$dato["cxstent"],
          "cxnrostent" =>$dato["cxnrostent"],
          "cxstentfarm" =>$dato["cxstentfarm"],
          "cxnrostentfarm" =>$dato["cxnrostentfarm"],
          "diag" =>$dato["diag"],
          "diagnrototal" =>$dato["diagnrototal"],
          "diagstent" =>$dato["diagstent"],
          "diagnrostent" =>$dato["diagnrostent"],
          "diagstentfarm" =>$dato["diagstentfarm"],
          "diagnrostentfarm" =>$dato["diagnrostentfarm"],
          "fecha_proceso" =>$dato["fecproceso"],
          "usr_proceso" =>$dato["usrproceso"],
          "medico" => $dato["nombre"]   
        );
        if ($id) {
            $datos[$id] = $atc;
        }
    }
    $respuesta = array(
      "ec_atc" => $datos
    );
  } else {
    $respuesta = array(
      "ec_atc" => NULL
    );
  }
} else {
  $codigo = -22;
  $descripcion = "Error en los datos";
}

} else {
  $codigo = -21;
  $descripcion = "Faltan argumentos requeridos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
