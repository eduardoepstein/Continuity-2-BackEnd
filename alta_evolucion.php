<?php
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
$fecha = reqtrim("fecha");
$evolucion = reqtrim("evolucion");
$usr_proceso = reqtrim("usr_proceso") ?? '';
$fc = nnreqcleanint("fc");
$pas = nnreqcleanint("pas");
$pad = nnreqcleanint("pad");
$peso = nnreqcleanint("peso");
$altura = nnreqcleanint("altura");
$per_cintura = nnreqcleanint("percintura");
$examen_fisico = reqtrim("exfisico");
$cerrado = nnreqcleanint("cerrado");
$problemas = nreqcleanarray("problemas");

$codigo = 0;
$descripcion = "OK";  
$mydatos = NULL;
$mydatos2 = NULL;
$respuesta = array();

if ($id || ($id_paciente && $evolucion)) {
  if($id) {
    //busco que exista y respete el tiempo y usuario
    $sql = "
      select  
        evl.id,
        evl.idpaciente,
        evl.fecha,
        evl.usrproceso
      from pac_evoluciones  evl    
      where evl.id = ? and evl.usrproceso = ? and datediff(SYSDATE(),evl.fecha) = 0
      ";
    $params = array("is",&$id,&$usr_proceso);
    $mydatos = my_query($sql, $params);
    if ($mydatos !== false) {
      if($mydatos["datos"]) {    
        $sql = "
        update
          pac_evoluciones
        set
          evolucion = ?,
          fecproceso = SYSDATE()        
        where
          id = ?
        ";
        $params = array("si",&$evolucion,&$id);          
        $mydatos2 = my_query($sql,$params,false);          
        if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {
          $respuesta = array(
            "id" => $id
          );    
        } else{
          $codigo = -24;
          $descripcion = "Error al actualizar la evolucion";
        }
      } else {
        $codigo = -23;
        $descripcion = "Error al actualizar la evolucion. El registro no se puede modificar";
      }
    } else {
      $codigo = -22;
      $descripcion = "Error en los datos";
    }
  } else {
    //inserto    
    $sql = "
    insert into
      pac_evoluciones(idpaciente,evolucion,usrproceso)        
      values(?,?,?)    
    ";    
    $params = array("iss",&$id_paciente,&$evolucion,&$usr_proceso);    
    $mydatos = my_query($sql,$params,false);

    if($mydatos == true && $mydatos["insert_id"]){
    $id = $mydatos["insert_id"];   
      $sql = "
      insert into
        pac_consultas(
          idpaciente,
          fecha,
          fc,
          pas,
          pad,
          peso,
          altura,
          percintura,
          exfisico,                    
          usrproceso
        )
      values(?,SYSDATE(),?,?,?,?,?,?,?,?)
      ";
      $params = array("iiiiiiiss",&$id_paciente,&$fc,&$pas,&$pad,&$peso,&$altura,&$per_cintura,&$examen_fisico,&$usr_proceso);
      $mydatos2 = my_query($sql,$params,false);
      if($problemas){
        foreach ($problemas as $prb) {        
          $sql = "
          insert into
            pac_problemasevolucion(idevolucion,idproblema) values(?,?) ";
          $params = array("ii",&$id,&$prb);          
          $mydatos3 = my_query($sql,$params,false);       
        }    
      }      
      if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {
        $respuesta = array(
          "id" => $id
        );    
      } else{
        $codigo = -23;
        $descripcion = "Error al tomar el turno";
      }
    } else {
      $codigo = -22;
      $descripcion = "Error en los datos";
    }
  }
} else {
  $codigo = -21;
  $descripcion = "Faltan argumentos requeridos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);