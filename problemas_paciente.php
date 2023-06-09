<?php
$respuesta = array();

$id_paciente = nnreqcleanint("id_paciente");

if (!is_null($id_paciente)) {  
  $sql = "
  select
    prb.id,
    prb.idpaciente,    
    prb.fecDesde,
    prb.fecHasta,
    prb.activo,
    cie.id idcie10,
    cie.codigo,
    cie.descripcion
  from
    pac_problemas prb
    inner join cie10 cie on cie.id = prb.idcie10
  where idpaciente = ?   
  order by fecproceso desc
";
$params = array("i",&$id_paciente); 
      

$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  if($mydatos["datos"]){
    $id_problema = -1; 
    foreach ($mydatos["datos"] as $dato) {
      $problema = array(
      "id" => $dato["idcie10"],
      "codigo" => $dato["codigo"],
      "descripcion" => $dato["descripcion"],
      "fecha_desde" => $dato["fecDesde"],
      "fecha_hasta" => $dato["fecHasta"],
      "activo" => $dato["activo"]);      
      $id_problema = $dato["id"];        
      $datos[$id_problema] = $problema;
    }
    $respuesta = array(
      "problemas" => $datos
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
