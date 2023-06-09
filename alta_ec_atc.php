<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
$fecha = nreqtrim("fecha");
$da = nnreqcleanint("da");
$danrototal = nnreqcleanint("danrototal");
$dastent = nnreqcleanint("dastent");
$danrostent = nnreqcleanint("danrostent");
$dastentfarm = nnreqcleanint("dastentfarm");
$danrostentfarm = nnreqcleanint("danrostentfarm");
$cd = nnreqcleanint("cd");
$cdnrototal = nnreqcleanint("cdnrototal");
$cdstent = nnreqcleanint("cdstent");
$cdnrostent = nnreqcleanint("cdnrostent");
$cdstentfarm = nnreqcleanint("cdstentfarm");
$cdnrostentfarm = nnreqcleanint("cdnrostentfarm");
$cx = nnreqcleanint("cx");
$cxnrototal = nnreqcleanint("cxnrototal");
$cxstent = nnreqcleanint("cxstent");
$cxnrostent = nnreqcleanint("cxnrostent");
$cxstentfarm = nnreqcleanint("cxstentfarm");
$cxnrostentfarm = nnreqcleanint("cxnrostentfarm");
$diag = nnreqcleanint("diag");
$diagnrototal = nnreqcleanint("diagnrototal");
$diagstent = nnreqcleanint("diagstent");
$diagnrostent = nnreqcleanint("diagnrostent");
$diagstentfarm = nnreqcleanint("diagstentfarm");
$diagnrostentfarm = nnreqcleanint("diagnrostentfarm");
$usr_proceso = reqtrim("usr_proceso") ?? '';

if ($id || ($id_paciente && $usr_proceso)) {
$where = $id ? " where ec.id = ? ": 
  (($id_paciente && $fecha) ? " where ec.idpaciente = ? and ec.fecha = cast(? as datetime(6)) " :
    " where ec.idpaciente = ? and ec.fecha = CURDATE() ");
  $sql = "
  select  
    ec.id,
    ec.idpaciente,
    ec.fecha,
    ec.usrproceso
  from ec_atc  ec    
  {$where}
  order by id desc
  ";    
  $params = $id ? array("i",&$id) : 
    (($id_paciente && $fecha) ? array("is",&$id_paciente,&$fecha) : 
      array("i",&$id_paciente));      
  $mydatos = my_query($sql, $params);  
  print($sql);
  print_r($params);
  print_r($mydatos);
  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";    
    $mydatos2 = NULL;  
    if ($mydatos["datos"]) {   
      $id = $id ?? $mydatos["datos"][0]["id"];             
      $sql = "
      update
        ec_atc
      set
        idpaciente = ?,
        fecha = CURDATE(),
        da = ?,
        danrototal = ?,
        dastent = ?,
        danrostent = ?,
        dastentfarm = ?,
        danrostentfarm = ?,
        cd = ?,
        cdnrototal = ?,
        cdstent = ?,
        cdnrostent = ?,
        cdstentfarm = ?,
        cdnrostentfarm = ?,
        cx = ?,
        cxnrototal = ?,
        cxstent = ?,
        cxnrostent = ?,
        cxstentfarm = ?,
        cxnrostentfarm = ?,
        diag = ?,
        diagnrototal = ?,
        diagstent = ?,
        diagnrostent = ?,
        diagstentfarm = ?,
        diagnrostentfarm = ?,
        fecproceso = SYSDATE(),      
        usrproceso = ?        
      where
        id = ?
      ";
      $params = array("iiiiiiiiiiiiiiiiiiiiiiiiisi"
      ,&$id_paciente,&$da,&$danrototal,&$dastent,&$danrostent,&$dastentfarm
      ,&$danrostentfarm,&$cd,&$cdnrototal,&$cdstent,&$cdnrostent
      ,&$cdstentfarm,&$cdnrostentfarm,&$cx,&$cxnrototal,&$cxstent
      ,&$cxnrostent,&$cxstentfarm,&$cxnrostentfarm,&$diag,&$diagnrototal
      ,&$diagstent,&$diagnrostent,&$diagstentfarm,&$diagnrostentfarm,&$usr_proceso,&$id);  
    } else {
      $values = $fecha ? "values(?,?,?,?,?,?
      ,?,?,?,?,?
      ,?,?,?,?,?
      ,?,?,?,?,?
      ,?,?,?,?,SYSDATE(),?,?)" : "values(?,?,?,?,?,?
      ,?,?,?,?,?
      ,?,?,?,?,?
      ,?,?,?,?,?
      ,?,?,?,?,SYSDATE(),?,CURDATE())";
      $sql = "
      insert into
        ec_atc(
          idpaciente,          
          da,
          danrototal,
          dastent,
          danrostent,
          dastentfarm,
          danrostentfarm,
          cd,
          cdnrototal,
          cdstent,
          cdnrostent,
          cdstentfarm,
          cdnrostentfarm,
          cx,
          cxnrototal,
          cxstent,
          cxnrostent,
          cxstentfarm,
          cxnrostentfarm,
          diag,
          diagnrototal,
          diagstent,
          diagnrostent,
          diagstentfarm,
          diagnrostentfarm,
          fecproceso,
          usrproceso,
          fecha
        )
      {$values}
      ";      
      $params = array("iiiiiiiiiiiiiiiiiiiiiiiiis"
      ,&$id_paciente,&$da,&$danrototal,&$dastent,&$danrostent,&$dastentfarm
      ,&$danrostentfarm,&$cd,&$cdnrototal,&$cdstent,&$cdnrostent
      ,&$cdstentfarm,&$cdnrostentfarm,&$cx,&$cxnrototal,&$cxstent
      ,&$cxnrostent,&$cxstentfarm,&$cxnrostentfarm,&$diag,&$diagnrototal
      ,&$diagstent,&$diagnrostent,&$diagstentfarm,&$diagnrostentfarm,&$usr_proceso);  
      if($fecha) {
        $params[0] = count($params) == 0 ? "s" : $params[0] . "s";    
        $params[count($params)] = &$fecha;       
      }
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