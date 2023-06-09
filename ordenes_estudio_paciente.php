<?php
$respuesta = array();
$id_paciente = nnreqcleanint("id_paciente");
if (!is_null($id_paciente)) {    
  $sql = "
    select  
    ode.id,
    ode.idpaciente id_paciente,
    ode.texto,    
    ode.idmedico id_medico,
    ode.idsucursal id_sucursal,
    ode.fecha,
    ode.diagnostico,
    ode.fecproceso fecha_proceso,
    ode.usrproceso usr_proceso,
    usr.nombre medico
    from pac_ordenesestudios ode
    left join usuarios usr on usr.id = ode.idmedico
  where ode.idpaciente = ?
  order by ode.fecha desc
";
$params = array("i",&$id_paciente);
$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  if($mydatos["datos"]){  
    $datos = array();
    foreach ($mydatos["datos"] as $dato) {           
    
    $orden = array(
      "id_paciente" => $dato["id_paciente"],
      "texto" => $dato["texto"],
      "id_medico" => $dato["id_medico"],
      "medico" => $dato["medico"],
      "id_sucursal" => $dato["id_sucursal"],
      "fecha" => $dato["fecha"],
      "diagnostico" => $dato["diagnostico"],
      "fecha_proceso" => $dato["fecha_proceso"],
      "usr_proceso" => $dato["usr_proceso"]
    );
    $datos[$dato["id"]] = $orden;    
    $respuesta = array(
      "ordenes_estudio" => $datos
    );
    } 
  } else {
    $respuesta = array(
      "ordenes_estudio" => null
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
