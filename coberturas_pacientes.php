<?php
$respuesta = array();

$id_paciente = nnreqcleanint("id_persona");
$fecha = nreqtrim("fecha");
$id = nreqtrim("id");

if (!is_null($id_paciente)) {
  //pac_coberturas.IX_PAC_Coberturas key paciente+fecha  
  $where = $id ? "where id = ?" : ($fecha ? "where idpaciente = ? and fecha = ?" : "where idpaciente = ?");    
  $sql = "
  select
    id,
    idpaciente,
    idcobertura co_codigo,
    idplan pl_id,
    nroafiliado pa_afiliado,
    idivacobertura pl_cobertura,
    fecha pl_fecha
  from
    pac_coberturas
  {$where}    
  order by fecha desc
";
$params = $id ? array("i",&$id) : 
      ($fecha ? array("is",&$id_paciente,&$fecha) :      
      array("i",&$id_paciente));

$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  if($mydatos["datos"]){
       
    $coberturas = array();
    $id_cobertura = -1;
    $id_plan = -1;
    $datos = array();
    $dat = NULL;
    $dat2 = NULL;
    foreach ($mydatos["datos"] as $cob) {
      $id_cob = $cob["co_codigo"];
      $id_pla = $cob["pl_id"];
      
      $dat2 = array(
      "pl_key" => $cob["id"],
      "pl_afiliado" => $cob["pa_afiliado"],
      "pl_cobertura" => $cob["pl_cobertura"],
      "pl_fecha" => $cob["pl_fecha"]);      

      if ($id_cob != $id_cobertura) { 
        
        $id_cobertura = $id_cob;        
        $datos[$id_cobertura] = array(          
          "planes" => array(
            $cob["pl_id"] => array($dat2)
          )
        );
      } else {        
        if($id_pla != $id_plan){      
          $id_plan = $id_pla;       
          $datos[$id_cobertura]["planes"][$id_plan] = array($dat2);          
        }else{
          array_push($datos[$id_cobertura]["planes"][$id_plan],$dat2);         
        }
        
      }
    }
    $respuesta = array(
      "coberturas" => $datos
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
