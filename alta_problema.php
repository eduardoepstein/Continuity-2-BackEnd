<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
$id_cie10 = nnreqcleanint("id_cie10");
$activo = nnreqcleanint("activo") ?? 1;
$usr_proceso = nreqtrim("usr_proceso");

if ($id || ($id_paciente && $id_cie10)) {
  $where = $id ? " where id = ? " : " where idpaciente = ? and idcie10 = ? ";    
  $sql = "
  select
    id,
    idpaciente,
    idcie10,
    fecdesde,
    fechasta,
    activo
  from
    pac_problemas
  {$where}
";
$params = $id ? array("i",&$id) : array("ii",&$id_paciente,&$id_cie10);
$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  $mydatos2 = NULL;  
  if ($mydatos["datos"]) {        
    if(count($mydatos["datos"]) == 1) {
      $iou = $mydatos["datos"][0]["id"];         
      $set = $activo == 0 ? "fechasta = CURDATE()," : "fechasta = null,";     
      $sql = "
        update
          pac_problemas
        set
          activo = ?,
          {$set}
          fecproceso = SYSDATE(),
          usrproceso = ?
        where
          id = ?        
      ";
      $params = array(
        "isi",                        
        &$activo,&$usr_proceso,&$iou
      );    
    }    
  } else {      
    if (!(is_null($id_paciente)) && !(is_null($id_cie10))) {            
      $sql = "
        insert into
          pac_problemas(
            idpaciente,idcie10,fecdesde,
            fecproceso,usrproceso
          )
          values(?,?,CURDATE(),SYSDATE(),?)        
      ";
      $params = array("iis", &$id_paciente,&$id_cie10,&$usr_proceso);
    }
  }    
  $mydatos2 = my_query($sql,$params,false);  
  if ($mydatos2 !== false) {  
    if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {      
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
