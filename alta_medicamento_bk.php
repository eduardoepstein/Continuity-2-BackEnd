<?php
$respuesta = array();
$id = nnreqcleanint("id"); //idmedicacion
$id_paciente = nnreqcleanint("id_paciente"); 
$codigo_medicamento = nnreqcleanint("codigo");
$fecha_hasta = null;// = nreqtrim("fecha_hasta");
$fecha_desde = null;// = nreqtrim("fecha_desde");
$activo = nnreqcleanint("activo") ?? 1;
$usr_proceso = nreqtrim("usr_proceso") ?? '';
$hora_inicial = nnreqcleanint("hora_inicial") ?? 8;
$frecuencia = nnreqcleanint("frecuencia");
$cantidad = nnreqcleanint("cantidad");
$observaciones = nreqtrim("observaciones");
$unidad = nreqtrim("unidad"); //tld_codunidad

$tratramiento = nreqtrim("tratamiento"); //0 cronico, 1 agudo

if ($id || ($id_paciente && $codigo_medicamento)) {    
  $tratramiento = !is_null($tratramiento) ? (($tratramiento == 0) ? 365 : 30) : null;    
  $where = $id ? " where id = ? " : " where idpaciente = ? and codmedicamento = ? ";    
  $sql = "
  select
    id,
    idpaciente,
    codmedicamento,
    fecdesde,
    fechasta    
  from
    pac_medicacion
  {$where}
  order by id desc
";
$params = $id ? array("i",&$id) : array("ii",&$id_paciente,&$codigo_medicamento);
$mydatos = my_query($sql, $params);
if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  $mydatos2 = NULL;  
  $mydatos3 = NULL;  
  if ($mydatos["datos"]) {                
    $id = $id ?? $mydatos["datos"][0]["id"];            
    $set = $tratramiento ? "fechasta = adddate(curdate(),?),": ($activo == 0 ? "fechasta = CURDATE()," : "fechasta = null,");    
    $sql = "
      update
        pac_medicacion
      set          
        {$set}
        fecproceso = SYSDATE(),
        usrproceso = ?
      where
        id = ?        
    ";  
    $params = $tratramiento ? array("isi",&$tratramiento,&$usr_proceso,&$id) : array("si",&$usr_proceso,&$id);     
    $mydatos2 = my_query($sql,$params,false);    
    if($mydatos2 == true && $mydatos2["filas_afectadas"] == 1) {            
      $sql = "      
        update
          pac_detallemedicacion
        set          
          fecha = CURDATE(),
          frecuencia = ?,
          cantidad = ?,
          horainicial = ?,
          fecproceso = SYSDATE(),
          usrproceso = ?,
          observaciones = ?,
          tld_codunidad = ?
        where
          idmedicacion = ?        
      ";
      $params = array("iiissii",&$frecuencia,&$cantidad,&$hora_inicial,&$usr_proceso,&$observaciones,&$unidad,&$id);
    } else {
      $codigo = -22;
      $descripcion = "Error en los datos";
    }
  } else {      
    if (!(is_null($id_paciente)) && !(is_null($codigo_medicamento))) {        
      $values = $tratramiento ? "adddate(curdate(),?)":"null";
      $sql = "
        insert into
          pac_medicacion(
            idpaciente,codmedicamento,fecdesde,fechasta,
            fecproceso,usrproceso
          )
          values(?,?,CURDATE(),{$values},SYSDATE(),?)        
      ";      
      $params = $tratramiento ? array ("iiis", &$id_paciente,&$codigo_medicamento,&$tratramiento,&$usr_proceso) : array ("iis", &$id_paciente,&$codigo_medicamento,&$usr_proceso);            
      $mydatos2 = my_query($sql,$params,false);               
      if($mydatos2 == true && $mydatos2["insert_id"]) {         
        $id = $mydatos2["insert_id"] ?? -1;       
        $set = $fecha_desde ? "fecha = ?," : "fecha = CURDATE(),";     
        $sql = "
        insert into
        pac_detallemedicacion(
            idmedicacion,fecha,frecuencia,cantidad,horainicial,
            fecproceso,usrproceso,observaciones,tld_codunidad
          )
          values(?,CURDATE(),?,?,?,SYSDATE(),?,?,?)    
        ";
        $params = array("iiiissi",&$id,&$frecuencia,&$cantidad,&$hora_inicial,&$usr_proceso,&$observaciones,&$unidad);        
      } else {
        $codigo = -24;
        $descripcion = "El medicamento ya fue ingresado";  
      }
    } else {
      $codigo = -22;
      $descripcion = "Error en los datos";
    }
  }  
  $mydatos3 = my_query($sql,$params,false);     
  if($mydatos3 == true && ($mydatos3["insert_id"] || ($mydatos3["filas_afectadas"] == 1))) {      
      $respuesta = array(
        "id" => $id
      );    
  } else {
    $codigo = -23;
    $descripcion = "Error al realizar la operación";  
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
