<?php
$respuesta = array();
$id = nnreqcleanint("id"); //idmedicacion
$id_paciente = nnreqcleanint("id_paciente"); 
$texto = nreqtrim("texto");
$id_medico = nnreqcleanint("id_medico");
$id_sucursal = nnreqcleanint("id_sucursal");
$fecha = nreqtrim("fecha");
$diagnostico = nreqtrim("diagnostico");
$fecha_proceso = nreqtrim("fecha_proceso");
$usr_proceso = nreqtrim("usr_proceso");

if ($usr_proceso && ($id || ($id_paciente && $fecha))) {        
  $where = $id ? " where ode.id = ? and ode.usrproceso = ?" : " where ode.idpaciente = ? and ode.fecha = ? and ode.usrproceso = ?";    
  $sql = "
  select  
    ode.id,
    ode.idpaciente id_paciente,
    ode.texto,    
    ode.idmedico id_medico,
    ode.idsucursal id_sucursal,
    ode.fecha,
    ode.diagnostico,
    ode.fecproceso fecha_proceso,
    ode.usrproceso usr_proceso  
  from pac_ordenesestudios ode
  {$where}
  order by id desc
";
$params = $id ? array("is",&$id,&$usr_proceso) : array("iss",&$id_paciente,&$fecha,&$usr_proceso);
$mydatos = my_query($sql, $params);
if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  $mydatos2 = NULL;  
  $mydatos3 = NULL;  
  if ($mydatos["datos"]) {                
    $id = $id ?? $mydatos["datos"][0]["id"];                
    $sql = "
      update
        pac_ordenesestudios
      set         
        text = ?
        idsucursal = ?
        diagnostico = ?
        fecproceso = SYSDATE(),
        usrproceso = ?
      where
        id = ?        
    ";  
    $params = array ("isiissi",&$texto,&$id_sucursal,&$diagnostico,&$usr_proceso,&$id);            
  } else {      
    if (!(is_null($id_paciente)) && !(is_null($texto))) {              
      $sql = "
        insert into
        pac_ordenesestudios
            idpaciente,texto,idmedico,idsucursal,fecha,diagnostico
            fecproceso,usrproceso
          )
          values(?,?,?,?,CURDATE(),?,SYSDATE(),?)        
      ";      
      $params = array ("isiiss", &$id_paciente,&$texto,&$id_medico,&$id_sucursal,&$diagnostico,&$usr_proceso);            
      
    } else {
      $codigo = -22;
      $descripcion = "Error en los datos";
    }
  }  
  $mydatos2 = my_query($sql,$params,false);      
  if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {      
      $respuesta = array(
        "id" => $id
      );    
  } else{
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
