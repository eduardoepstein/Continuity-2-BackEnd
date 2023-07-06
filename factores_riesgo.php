<?php
$respuesta = array();

$id_paciente = nnreqcleanint("id_paciente");

if (!is_null($id_paciente)) {  
  $sql = "
  select
    fac.id,    
    fac.idpaciente id_paciente,    
    fac.dislipemia,
    fac.obesidad,
    fac.tabaquismo,
    fac.tabacopasivo,
    fac.extabaquista,
    fac.hipertension,
    fac.sedentarismo,
    fac.diabetes,
    fac.antfamiliares,
    fac.estres,
    fac.dietainadecuada,
    fac.climaterio,
    fac.tratcolesterol,
    fac.tratdiabetes,
    fac.trathipertension,
    fac.prevencionsec
  from
    pac_factoresderiesgo fac     
  where idpaciente = ?   
";
$params = array("i",&$id_paciente);       
$mydatos = my_query($sql, $params);
if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  if($mydatos["datos"]){        
    $dato = $mydatos["datos"][0];
    //tomo todos los elementos del mapa desde el 2, dejo fuera id y id_paciente
    $factores = array_slice($dato,2);
    $datos =  array(
      "id" => $dato["id"],
      "id_paciente" => $dato["id_paciente"],
      "factores" => $factores
    );     
    $respuesta = array(
      "factores_riesgo" => $datos
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
