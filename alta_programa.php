<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
$id_programa = nnreqcleanint("id_programa");
$usr_proceso = reqtrim("usr_proceso") ?? '';

if ($id || ($id_paciente && $usr_proceso && $id_programa)) {
$where = $id ? " where prg.id = ? ": 
  ($id_paciente && $id_programa ? " where prg.idpaciente = ? and prg.idtipodeestudio = ?" :
    null);
  $sql = "
  select
    prg.id,
    prg.idpaciente,
    prg.idtipodeestudio
  from pac_programas  prg
  {$where}
  order by id desc
  ";      
  $params = $id ? array("i",&$id) : 
    (($id_paciente && $id_programa) ? array("ii",&$id_paciente,&$id_programa) : 
      null);      
  $mydatos = my_query($sql, $params);    
  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";    
    $mydatos2 = NULL;  
    if ($mydatos["datos"]) {   
      $id = $id ?? $mydatos["datos"][0]["id"];             
      $sql = "
      update
      pac_programas
      set
        idpaciente = ?,
        idtipodeestudio = ?,
        fecproceso = SYSDATE(),      
        usrproceso = ?        
      where
        id = ?
      ";
      $params = array("iisi"
      &$id_paciente,&$id_programa,&$usr_proceso,&$id);  
    } else {
      $sql = "
      insert into
      pac_programas(
          idpaciente,          
          idtipodeestudio,
          usrproceso
        )
      values(?,?,?)
      ";      
      $params = array("iis"
      ,&$id_paciente,&$id_programa,&$usr_proceso);  
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