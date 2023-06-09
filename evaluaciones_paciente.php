<?php
$respuesta = array();
$id_paciente = nnreqcleanint("id_paciente");

if (!is_null($id_paciente)) {    
  $sql = "
    select  
      evl.id,
      evl.idpaciente,
      evl.fecha,
      evl.usrproceso,
      evl.fecproceso,
      evl.evtabla,
      evl.evId,
      tpe.id as id_evaluacion,
      tpe.descripcion
  from ev_maestro  evl
    inner join tiposdeevaluaciones tpe on  tpe.id = evl.tipoEvaluacionID
  where evl.idpaciente = ?
  order by evl.fecha desc
";

  $sql1 = "
  select    
    null as id,
    tur.idpaciente,    
    age.dia as fecha,
    tur.usrproceso,
    tur.fecproceso,
    null as evtabla,
    null as evId,
    tde.idtipoevaluacion as id_evaluacion,
    tde.descripcion 
  from
    cal_agenda age    
  inner join cal_turnos tur
    on age.id = tur.idagenda
  inner join cal_tiposdeestudios tde
    on tur.idtipodeestudio = tde.codigo
  where tur.idpaciente = ? and tde.idtipoevaluacion is not null and tur.idestado = 1
  order by age.hora ASC  
";

$params = array("i",&$id_paciente);
$mydatos = my_query($sql, $params);  
$mydatos1 = my_query($sql1, $params);

if ($mydatos !== false || $mydatos1 !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  $mydatos = $mydatos["datos"] ? $mydatos["datos"] : array();
  $mydatos = $mydatos1["datos"] ? array_merge($mydatos,$mydatos1["datos"]) : $mydatos; 
  $mydatos = array( "datos" => $mydatos);    
  if($mydatos["datos"]) {           
    $evaluaciones = array();    
    $pendientes = array();    
    foreach ($mydatos["datos"] as $dato) {     
      $id_evaluacion = $dato["id"];
      $evaluacion = array(        
        "id_paciente"  => $dato["idpaciente"],
        "fecha"  => $dato["fecha"] ? (new DateTime($dato["fecha"]))->format('Y-m-d') : null,            
        "usr_proceso"  => $dato["usrproceso"],
        "fecha_proceso"  => $dato["fecproceso"],
        "evtabla"  => $dato["evtabla"],
        "evtabla_Id"  => $dato["evId"],
        "id_evaluacion"  => $dato["id_evaluacion"],
        "descripcion"  => $dato["descripcion"]        
      );
      if($id_evaluacion){
        $evaluaciones[$id_evaluacion] = $evaluacion ?? null;     
      } else {
        $pendientes[] = $evaluacion ?? null;      
      }      
    }
    $respuesta = array(
      "evaluaciones" => $evaluaciones,
      "pendientes" => $pendientes,
    );
  } else {
    $codigo = -23;
    $descripcion = "Error en los datos";
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
