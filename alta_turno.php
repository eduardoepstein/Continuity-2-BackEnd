<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_agenda = nnreqcleanint("id_agenda");
$col = nnreqcleanint("col") ?? 1;
$id_estado = nnreqcleanint("id_estado") ?? 1;
$id_paciente = nnreqcleanint("id_paciente");
$telefono = nreqtrim("telefono");
$id_tipo_estudio = nnreqcleanint("id_tipo_estudio");
$id_tipo_cobertura = nnreqcleanint("id_tipo_cobertura") ?? 0;
$duracion = nnreqcleanint("duracion") ?? 0;
$facturar = nnreqcleanint("facturar") ?? 1;
$id_copago = nnreqcleanint("id_copago") ?? 0;
$id_posnet = nnreqcleanint("id_posnet");
$id_iva_cobertura = nnreqcleanint("id_iva_cobertura");
$importe = reqtrim("importe") ?? 0.00;
$requiere_autorizacion = nnreqcleanint("requiere_autorizacion") ?? 0;
$deleg = nreqtrim("deleg");
$nro_autorizacion = nreqtrim("nro_autorizacion");
$observaciones = nreqtrim("observaciones");
$med_seguimiento = nnreqcleanint("med_seguimiento");
$id_quien_envio = nnreqcleanint("id_quien_envio");
$comision = nnreqcleanint("comision") ?? 0;
$facturado = nnreqcleanint("facturado") ?? 0;
$refacturar = nnreqcleanint("refacturar") ?? 0;
$riesgo_vascular = nnreqcleanint("riesgo_vascular") ?? 0;
$web = nnreqcleanint("web") ?? 0;
$usr_proceso = reqtrim("usr_proceso") ?? '';
$id_usr_atendio = nnreqcleanint("id_usr_atendio");
$no_enviar_mail = nnreqcleanint("no_enviar_mail") ?? 0;
$mail_confirmacion_enviado = nnreqcleanint("mail_confirmacion_enviado") ?? 0;
$mail_cancelacion_enviado = nnreqcleanint("mail_cancelacion_enviado") ?? 0;
$devolucion_evaluacion = nnreqcleanint("devolucion_evaluacion") ?? 0;
$obs_paciente = nreqtrim("obs_paciente");
$sobreturno = reqtrim("sobreturno");

if ($id || ($id_agenda && $id_tipo_estudio && $id_paciente && $sobreturno && $id_estado)) {
  $where = $id ? "where id = ?" : "where idagenda = ? and idestado <> 2";    
  $sql = "
  select
    id,
    idpaciente,
    idtipodeestudio
  from
    cal_turnos    
  {$where}
";
$params = $id ? array("i",&$id) : array("i",&$id_agenda);

$mydatos = my_query($sql, $params);
$mydatos2 = $mydatos3 = array();
if ($mydatos !== false) {   
  $codigo = 0;
  $descripcion = "OK";

  if($mydatos["datos"] && !$id){
    //busco paciente entre los datos
    foreach($mydatos["datos"] as $turno){
      if($turno["idpaciente"] == $id_paciente){
        $id = $turno["id"];      
      }
    }
  }
  if($id){
    //actualizo
    $estado = $id_estado ? "?" : "(select est.idproximoestado from cal_estados est where est.codigo = (select idestado from cal_estadosturnos tur where tur.idturno = ? order by tur.fecproceso desc limit 1))";

     $sql = "
        update
        cal_turnos
        set
        idestado = ?,
        facturado = ?,
        refacturar = ?,
        nroautorizacion = ?,
        idusratendio = ?,
        observaciones = ?,
        obspaciente = ?,
        noenviarmail = ?,
        mailconfirmacionenviado = ?,
        mailcancelacionenviado = ?,
        devolucionevaluacion = ?,
        usrproceso = ?
        where
          id = ?
      ";
      $params = array(
        "iiiiissiiiisi",                        
        &$id_estado,&$facturado,&$refacturar,&$nro_autorizacion,
        &$id_usr_atendio,&$observaciones,&$obs_paciente,&$no_enviar_mail,&$mail_confirmacion_enviado,
        &$mail_cancelacion_enviado,&$devolucion_evaluacion,&$usr_proceso,&$id
      );  
      $mydatos2 = my_query($sql,$params,false);  
      if($mydatos2 == true && $mydatos2["filas_afectadas"] == 1) {        
        $sql = "
        insert into
          cal_estadosturnos(
            idturno,
            idestado,
            fecproceso,
            usrproceso
          )
        values(?,?,SYSDATE(),?)
        ";
        $params = array("iis",&$id,&$id_estado,&$usr_proceso);        
        $mydatos3 = my_query($sql,$params,false);
      }
  } elseif(!$mydatos["datos"] || ($sobreturno && count($mydatos["datos"]) < 3)){    
    //inserto
    $col = count($mydatos["datos"]) + 1;    
    $sql = "
    insert into
      cal_turnos(
        idagenda,col,idpaciente,idtipodeestudio,
        idestado,duracion,
        idtipocobertura,facturar,idcopago,idposnet,idivacobertura,importe,facturado,refacturar,
        reqautoriz,deleg,nroautorizacion,medseguimiento,idquienenvio,comision,idusratendio,
        observaciones,telefono,riesgovascular,web,obspaciente,
        noenviarmail,mailconfirmacionenviado,mailcancelacionenviado,devolucionevaluacion,usrproceso      
      )
      values(?,?,?,?,
      ?,?,
      ?,?,?,?,?,?,?,?,
      ?,?,?,?,?,?,?,
      ?,?,?,?,?,
      ?,?,?,?,?)
    ";    
    $params = array(
      "iiiiiiiiiiiiiiissiiiissiisiiiis",
      &$id_agenda,&$col,&$id_paciente,&$id_tipo_estudio
      ,&$id_estado,&$duracion
      ,&$id_tipo_cobertura,&$facturar,&$id_copago,&$id_posnet,&$id_iva_cobertura,&$importe,&$facturado,&$refacturar
      ,&$requiere_autorizacion,&$deleg,&$nro_autorizacion,&$med_seguimiento,&$id_quien_envio,&$comision,&$id_usr_atendio
      ,&$observaciones,&$telefono,&$riesgo_vascular,&$web,&$obs_paciente
      ,&$no_enviar_mail,&$mail_confirmacion_enviado,&$mail_cancelacion_enviado,&$devolucion_evaluacion,&$usr_proceso
    );    
    $mydatos2 = my_query($sql,$params,false);

    if($mydatos2 == true && $mydatos2["insert_id"]){
    $id = $mydatos2["insert_id"];   
      $sql = "
      insert into
        cal_estadosturnos(
          idturno,
          idestado,
          fecproceso,
          usrproceso
        )
      values(?,?,SYSDATE(),?)
      ";
      $params = array("iis",&$id,&$id_estado,&$usr_proceso);
      $mydatos3 = my_query($sql,$params,false);
    }
  } else{ //aunque entre acá se pisa con codigo -23
    $codigo = -24;    
  }  
  if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {
    $respuesta = array(
      "id" => $id
    );    
  } elseif($codigo = -24) {
    $descripcion = "Turno ya asignado";
  } else{
    $codigo = -23;
    $descripcion = "Error al tomar el turno";
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