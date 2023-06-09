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
  ec.nrototalpuentes,
  ec.da,
  ec.dapuentearterial,
  ec.dapuentevenoso,
  ec.cd,
  ec.cdpuentearterial,
  ec.cdpuentevenoso,
  ec.cx,
  ec.cxpuentearterial,
  ec.cxpuentevenoso,
  ec.fecproceso,
  ec.usrproceso,
  usr.nombre
    from ec_crm ec
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
        $crm = array(                    
          "id_paciente" => $dato["idpaciente"],
          "fecha" => $dato["fecha"],
          "nrototalpuentes" => $dato["nrototalpuentes"],
          "da" => $dato["da"],
          "dapuentearterial" => $dato["dapuentearterial"],
          "dapuentevenoso" => $dato["dapuentevenoso"],
          "cd" => $dato["cd"],
          "cdpuentearterial" => $dato["cdpuentearterial"],
          "cdpuentevenoso" => $dato["cdpuentevenoso"],
          "cx" => $dato["cx"],
          "cxpuentearterial" => $dato["cxpuentearterial"],
          "cxpuentevenoso" => $dato["cxpuentevenoso"],
          "fecha_proceso" => $dato["fecproceso"],
          "usr_proceso" => $dato["usrproceso"],
          "medico" => $dato["nombre"]                    
        );
        if ($id) {
            $datos[$id] = $crm;
        }
    }
    $respuesta = array(
      "ec_crm" => $datos
    );
  } else {
    $respuesta = array(
      "ec_crm" => NULL
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
