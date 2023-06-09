<?php
$respuesta = array();

$id_turno = nnreqcleanint("id_turno");

$paciente = nnreqcleanint("id_paciente");
$tipo_estudio = nnreqcleanint("tipo_estudio");
$especialidad = nnreqcleanint("especialidad");
$fecha = nreqtrim("fecha");

//$where = $seteo_turno ? " where set.id = ?" : "where 1";
if($id_turno || $paciente)
{ 
  $where = $id_turno ? "where tur.id = ?" : (
    $paciente ? $tipo_estudio ? "where tur.idpaciente = ? and est.idtipodeestudio = ?" : (
      $especialidad ? "where tur.idpaciente = ? and esp.idespecialidad = ?" : (
        "where tur.idpaciente = ?"
      )): (
      null
    ));

  $where = $fecha ? $where . " and age.dia = ?" : $where;

  $sql_ = $tipo_estudio ? "
          est.idtipodeestudio as id_tipo_estudio," : "
          tur.idtipodeestudio as id_tipo_estudio,
          ";
  
  $join = $tipo_estudio ? " left outer join cal_seteosestudiosmedicos est
          on est.idseteoturno = sst.id
          " : 
          NULL;

  $sql = "
  select    
    sst.id as id_seto,
    sst.idmedico,
    sst.diasemana,
    age.idsucursal,
    sst.idconsultorio,    
    sst.horainicio,
    sst.horafin,    
    age.id as id_agenda,
    age.dia as dia_agenda,    
    tur.id as id_turno,
    tur.idestado,
    tur.idpaciente,
    {$sql_}    
    tur.idtipocobertura,
    tur.idivacobertura,
    tur.facturar,
    tur.idcopago,
    tur.idposnet,
    tur.importe,
    tur.facturado,
    esp.idespecialidad as id_especialidad,
    etur.idestado as id_estados_turnos,
    etur.fecproceso as fecha_estados_turnos
  from
    cal_seteosturnos sst
  inner join cal_agenda age
    on age.idseteoturno = sst.id
  left outer join cal_turnos tur
    on tur.idagenda = age.id
  left outer join cal_especialidadesturnos esp 
    on esp.idseteoturno = sst.id
  left outer join cal_estadosturnos etur
    on etur.idturno = tur.id
  {$join}
  {$where}
  order by age.dia ASC  
  ";  
  
  $params = $id_turno ? array("i",&$id_turno) : (
    $paciente ? $tipo_estudio ? array("ii",&$paciente,&$tipo_estudio) : (
      $especialidad ? array("ii",&$paciente,&$especialidad) : (
        array("i",&$paciente)
      )): (
      array()
    ));

  if($fecha) {
    $params[0] = count($params) == 0 ? "s" : $params[0] . "s";    
    $params[count($params)] = &$fecha;       
  }  

  $mydatos = my_query($sql, $params);  
  
  if ($mydatos !== false) {    
    $codigo = 0;  
    $descripcion = "OK";    
    $datos = array();
    $last_date = NULL;
    $last_i = 0;
    $size = 0;
    if ($mydatos["datos"]) {      
      //elimino dias incompletos
      $datos = $mydatos["datos"];
      //
      $id = -1;      
      $id_agendas = array();
      $agrupador_agenda = -1;
      $agrupador_turno = -1;
      $agendas_turnos = array();      
      $agendas = NULL;
      $turnos = NULL;
      $sobreturnos = NULL;
      
      foreach ($datos as $agenda) {        
        $id_agrupador_turno = $agenda["id_turno"];        
        $id_agenda = $agenda["id_agenda"];        
        $id_agrupador_agenda = $agenda["idmedico"] . $agenda["idconsultorio"] . strtotime($agenda["dia_agenda"]);

        $estado_turno = $agenda["id_estados_turnos"] ? array(
          "estado_turno" => $agenda["id_estados_turnos"],
          "estado_fecha" => $agenda["fecha_estados_turnos"]
        ) : null;

        $sobreturnos = array(                               
          "id_paciente"  => $agenda["idpaciente"],
          "apellido_paciente"  => $agenda["apellido"] ?? null,
          "id_estado"  => $agenda["idestado"],
          "id_tipo_cobertura"  => $agenda["idtipocobertura"],
          "id_iva_cobertura"  => $agenda["idivacobertura"],
          "facturar"  => $agenda["facturar"],
          "id_copago"  => $agenda["idcopago"],
          "id_posnet"  => $agenda["idposnet"],
          "importe"  => $agenda["importe"],
          "facturado"  => $agenda["facturado"],
          "estados" => $estado_turno ?? null
        );

        $turnos = array(
          "hora_turno"  => $agenda["dia_hora"] ? (new DateTime($agenda["dia_hora"]))->format('H:i:s') : null,                    
          "sobreturnos" => $agenda["id_turno"] ? array(
            $agenda["id_turno"] => $sobreturnos
          ) : null);

        if($agrupador_agenda != $id_agrupador_agenda) {
          $agrupador_agenda = $id_agrupador_agenda;

          $agendas[$agrupador_agenda] = array(
            "id_seteo"  => $agenda["id_seto"],
            "id_medico"  => $agenda["idmedico"],                     
            "id_sucursal"  => $agenda["idsucursal"],
            "id_consultorio"  => $agenda["idconsultorio"],
            "dia_semana"  => $agenda["diasemana"],
            "dia_agenda"  => $agenda["dia_agenda"] ? (new DateTime($agenda["dia_agenda"]))->format('Y-m-d') : null,            
            "hora_inicio"  => $agenda["horainicio"] ? (new DateTime($agenda["horainicio"]))->format('H:i:s') : null,            
            "hora_fin"  => $agenda["horafin"] ? (new DateTime($agenda["horafin"]))->format('H:i:s') : null,            
            "duracion"  => $agenda["duracion"],
            "grupal"  => $agenda["grupal"], 
            "sobreturno"  => $agenda["sobreturno"],
            "id_tipo_estudio"  => $agenda["id_tipo_estudio"],
            "id_especialidad"  => $agenda["id_especialidad"],
            "turnos" => array(
              $agenda["id_agenda"] => $turnos
              )
          );
        } else {
          if($id != $id_agenda) {
            $id = $id_agenda;
            $agendas[$agrupador_agenda]["turnos"][$id_agenda] = $turnos;
          } else {
            if($agenda["id_turno"]){
              if($agrupador_turno != $id_agrupador_turno){
                $agrupador_turno = $id_agrupador_turno;
                $agendas[$agrupador_agenda]["turnos"][$id_agenda]["sobreturnos"][$agenda["id_turno"]] = $sobreturnos;
              }
              else{
                $agendas[$agrupador_agenda]["turnos"][$id_agenda]["sobreturnos"][$agenda["id_turno"]]["estados"][] = $estado_turno;
              }
            } else{
              //
            }            
          }
        }        
      }
      $respuesta = array(
        "agendas" => $agendas
      );
    } else {
      $agendas = array(
        "agendas" => NULL
    );
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