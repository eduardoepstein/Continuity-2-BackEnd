<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
$fecha = nreqtrim("fecha");
$nrototalpuentes = nnreqcleanint("nrototalpuentes");
$da = nnreqcleanint("da");
$dapuentearterial = nnreqcleanint("dapuentearterial");
$dapuentevenoso = nnreqcleanint("dapuentevenoso");
$cd = nnreqcleanint("cd");
$cdpuentearterial = nnreqcleanint("cdpuentearterial");
$cdpuentevenoso = nnreqcleanint("cdpuentevenoso");
$cx = nnreqcleanint("cx");
$cxpuentearterial = nnreqcleanint("cxpuentearterial");
$cxpuentevenoso = nnreqcleanint("cxpuentevenoso");
$usr_proceso = reqtrim("usr_proceso") ?? '';

if ($id || ($id_paciente && $usr_proceso)) {
$where = $id ? " where ec.id = ? ": 
  (($id_paciente && $fecha) ? " where ec.idpaciente = ? and ec.fecha = ? " :
    " where ec.idpaciente = ? and ec.fecha = CURDATE() ");
  $sql = "
  select  
    ec.id,
    ec.idpaciente,
    ec.fecha,
    ec.usrproceso
  from ec_crm  ec    
  {$where}
  order by id desc
  ";  
  
  $params = $id ? array("i",&$id) : 
    (($id_paciente && $fecha) ? array("is",&$id_paciente,&$fecha) : 
      array("i",&$id_paciente));      
  $mydatos = my_query($sql, $params);    
  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";    
    $mydatos2 = NULL;  
    if ($mydatos["datos"]) {   
      $id = $id ?? $mydatos["datos"][0]["id"];       
      $sql = "
      update
        ec_crm
      set
        idpaciente = ?,        
        nrototalpuentes = ?,
        da = ?,
        dapuentearterial = ?,
        dapuentevenoso = ?,
        cd = ?,
        cdpuentearterial = ?,
        cdpuentevenoso = ?,
        cx = ?,
        cxpuentearterial = ?,
        cxpuentevenoso = ?,        
        fecproceso = SYSDATE(),      
        usrproceso = ?        
      where
        id = ?
      ";
      $params = array("iiiiiiiiiiisi"
      ,&$id_paciente,&$nrototalpuentes,&$da,&$dapuentearterial,&$dapuentevenoso,
      &$cd,&$cdpuentearterial,&$cdpuentevenoso,&$cx,&$cxpuentearterial,&$cxpuentevenoso,
      &$usr_proceso,&$id);  
    } else {
      $values = $fecha ? "values(?,?,?,
      ?,?,?,?,?,?,?,?,SYSDATE(),?,?)" : 
      "values(?,?,?,?,
      ?,?,?,?,?,?,?,SYSDATE(),?,CURDATE())";  
      $sql = "
      insert into
        ec_crm (
          idpaciente,          
          nrototalpuentes,
          da,
          dapuentearterial,
          dapuentevenoso,
          cd,
          cdpuentearterial,
          cdpuentevenoso,
          cx,
          cxpuentearterial,
          cxpuentevenoso,
          fecproceso,
          usrproceso,
          fecha
        )
      {$values}";      
      $params = array("iiiiiiiiiiiis"
      ,&$id_paciente,&$nrototalpuentes,&$da,&$dapuentearterial,&$dapuentevenoso,
      &$cd,&$cdpuentearterial,&$cdpuentevenoso,&$cx,&$cxpuentearterial,&$cxpuentevenoso,
      &$usr_proceso);  
    }        
    if($fecha) {
      $params[0] = count($params) == 0 ? "s" : $params[0] . "s";    
      $params[count($params)] = &$fecha;       
    }
    $mydatos2 = my_query($sql,$params,false);  
    if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {      
      $id = $id ?? $mydatos2["insert_id"] ?? -1;               
      $respuesta = array(
        "id" => $id
      );    
    } else{
      $codigo = -23;
      $descripcion = "Error al cargar los datos";
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