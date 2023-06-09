<?php
$respuesta = array();

$id_paciente = nnreqcleanint("id_persona");
$cobertura = nnreqcleanint("cobertura");
$plan = nnreqcleanint("plan");
$numero_afiliado = nreqtrim("numero_afiliado");
$iva = nnreqcleanint("iva");
$usuario_modificacion = nreqtrim("usuario_modificacion");
$fecha = nreqtrim("fecha");
$id = nreqtrim("id");

if (!is_null($id_paciente)) {
  //pac_coberturas.IX_PAC_Coberturas key paciente+fecha
  $where = $id ? "where id = ?" : 
  ($fecha ? "where idpaciente = ? and fecha = ?" : 
  (($cobertura && $plan) ? "where idpaciente = ? and idcobertura = ? and idplan = ?" : "where idpaciente = ?"));    
  
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
      (($cobertura && $plan) ? array("iii",&$id_paciente,&$cobertura,&$plan):
      array("i",&$id_paciente)));

$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  $mydatos2 = NULL;  
  if ($mydatos["datos"]) {        
    if(count($mydatos["datos"]) == 1) {
      $iou = $mydatos["datos"][0]["id"];    
      $sql = "
        update
          pac_coberturas
        set
          nroafiliado = ?,
          idivacobertura = ?,        
          fecproceso = SYSDATE(),
          usrproceso = ?
        where
          id = ?        
      ";
      $params = array(
        "sisi",                        
        &$numero_afiliado,&$iva,&$usuario_modificacion,&$iou
      );    
    }    
  } else {      
    if (!(is_null($cobertura)) && !(is_null($plan))) {      
      $values = $fecha ? " values(?,?,?,?,?,?,SYSDATE(),?)" : " values(?,CURDATE(),?,?,?,?,SYSDATE(),?)";
      $sql = "
        insert into
          pac_coberturas(
            idpaciente,fecha,
            idcobertura,idplan,nroafiliado,idivacobertura,
            fecproceso,usrproceso
          )
        {$values}              
      ";
      $params = $fecha ? 
      array(
        "isiisis",
        &$id_paciente,&$fecha,&$cobertura,&$plan,&$numero_afiliado,&$iva,&$usuario_modificacion
      ) : array(
        "iiisis",
        &$id_paciente,&$cobertura,&$plan,&$numero_afiliado,&$iva,&$usuario_modificacion
      );
    }
  }    
  $mydatos2 = my_query($sql,$params,false);  
  if ($mydatos2 !== false) {  
    if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {
      print("que");
      $iou = $mydatos["datos"][0]["id"] ?? $mydatos2["insert_id"] ?? -1;       
      $respuesta = array(
        "id" => $iou
      );    
    } else {
      $codigo = -24;
      $descripcion = "Error en los datos";
    }
  } else{
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
