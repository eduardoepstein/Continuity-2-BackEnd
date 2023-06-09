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
  ec.q,
  ec.noq,
  ec.nc,
  ec.anterior,
  ec.anteroseptal,
  ec.inferior,      
  ec.apical,
  ec.anteriorextenso,
  ec.complicaciones,
  ec.`lateral`,
  ec.fecproceso,
  ec.usrproceso,
  usr.nombre
    from ec_iam ec
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
        $iam = array(                              
          "id_paciente" => $dato["idpaciente"],
          "fecha" => $dato["fecha"],
          "q" => $dato["q"],
          "noq" => $dato["noq"],
          "nc" => $dato["nc"],
          "anterior" => $dato["anterior"],
          "anteroseptal" => $dato["anteroseptal"],
          "inferior" => $dato["inferior"],
          "lateral" => $dato["lateral"],
          "apical" => $dato["apical"],
          "anteriorextenso" => $dato["anteriorextenso"],
          "complicaciones" => $dato["complicaciones"],
          "fecha_proceso" => $dato["fecproceso"],
          "usr_proceso" => $dato["usrproceso"], 
          "medico" => $dato["nombre"]                 
        );
        if ($id) {
            $datos[$id] = $iam;
        }
    }
    $respuesta = array(
      "ec_iam" => $datos
    );
  } else {
    $respuesta = array(
      "ec_iam" => NULL
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
