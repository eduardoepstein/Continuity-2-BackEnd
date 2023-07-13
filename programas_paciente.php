<?php
$respuesta = array();
$id_paciente = nnreqcleanint("id_paciente");
if ($id_paciente) {    
  $where = " where prg.idpaciente = ? ";  
  $sql = "
  select
    prg.id,
    prg.idpaciente,
    tde.codigo,
    tde.descripcion
  from pac_programas prg
  inner join cal_tiposdeestudios tde
  on tde.codigo = prg.idtipodeestudio
  {$where}
  order by tde.codigo desc
";
$params = array("i",&$id_paciente);       
$mydatos = my_query($sql, $params);
if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  $datos = array();
  if($mydatos["datos"]){             
    foreach ($mydatos["datos"] as $dato) {
     $datos[$dato["id"]] = array(
        "codigo" => $dato["codigo"],
        "descripcion" => $dato["descripcion"]  
     );
    }
    $respuesta = array(
      "id_paciente" =>  $mydatos["datos"][0]["idpaciente"],
      "programas" => $datos
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