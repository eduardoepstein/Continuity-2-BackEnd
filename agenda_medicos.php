<?php
$respuesta = array();

$seteo_turno = nnreqcleanint("seteo_turno");
$medico = nnreqcleanint("medico");
$consultorio = nnreqcleanint("consultorio");
$sucursal = nnreqcleanint("sucursal");
$paciente = nnreqcleanint("paciente");
$dia_semana = reqtrim("dia_semana");
$tipo_estudio = nnreqcleanint("tipo_estudio");
$especialidad = nnreqcleanint("especialidad");
$fecha = nreqtrim("fecha") ?? "CURDATE()";
$estado = nreqtrim("estado") ? nreqtrim("estado") : ( $paciente ? 1 : 0 );

$franja_horaria_inicio = nreqtrim("franja_horaria_inicio");
$franja_horaria_fin = nreqtrim("franja_horaria_fin");

global $TURNOSBUSQUEDAMAXIMO;
global $TURNOSBUSQUEDALIMITE;

//$where = $seteo_turno ? " where set.id = ?" : "where 1";
if($seteo_turno || $tipo_estudio || $especialidad || $medico || $paciente || $sucursal)
{  
  $where = "where age.activo = 1 and age.dia >= ?";
  $where = $seteo_turno ? $where . " and sst.id = ?" : 
          ($tipo_estudio ? $where . " and est.idtipodeestudio = ?" : 
          ($especialidad ? $where . " and esp.idespecialidad = ?" : $where));

  $where = $medico ? $where . " and sst.idmedico = ?" : $where;
  $where = $consultorio ? $where . " and sst.idconsultorio = ?" : ($sucursal ? $where . " and age.idsucursal = ?" : $where);
  $where = $dia_semana ? $where . " and sst.diasemana = ?" : $where;
  $where = $paciente ? $where . " and tur.idpaciente = ?" : $where;
  $where = $estado == 0 ? $where . " and tur.id is null" : $where;

  $where = $franja_horaria_inicio && $franja_horaria_fin ? $where . " and sst.horainicio >= ? and sst.horainicio < ?" : $where;

  $join = $tipo_estudio ? " left outer join cal_seteosestudiosmedicos est
          on est.idseteoturno = sst.id
          " : 
          NULL;
  /*$join = ($medico && $estado == 1) ? $join. " left outer join personas prs
          on prs.id = tur.idpaciente
          " : 
          $join;*/

$sql_ = $tipo_estudio ? "
          est.idtipodeestudio as id_tipo_estudio," : "
          tur.idtipodeestudio as id_tipo_estudio,
          ";
  
  /*$sql_ = ($medico && $estado == 1) ? $sql_. " prs.apellido,
  " : 
  $sql_;*/

  $sql = "
  select    
    sst.id as id_seto,
    sst.idmedico,
    sst.diasemana,
    age.idsucursal,
    sst.idconsultorio,
    sst.sobreturno,
    sst.grupal,
    sst.horainicio,
    sst.horafin,
    sst.duracion,    
    age.id as id_agenda,
    age.dia as dia_agenda,
    age.hora as dia_hora,
    tur.id as id_turno,
    tur.idestado,
    tur.idpaciente,
    tur.apellidopaciente,
    {$sql_}    
    tur.col,
    tur.idtipocobertura,
    tur.idivacobertura,
    tur.facturar,
    tur.idcopago,
    tur.idposnet,
    tur.importe,
    tur.facturado,
    esp.idespecialidad as id_especialidad 
  from
    cal_seteosturnos sst
  inner join cal_agenda age
    on age.idseteoturno = sst.id
  left outer join cal_turnos tur
    on tur.idagenda = age.id
  left outer join cal_especialidadesturnos esp 
    on esp.idseteoturno = sst.id
  {$join}
  {$where}
  order by age.dia ASC
  limit {$TURNOSBUSQUEDALIMITE}    
  ";
  $params = $seteo_turno ? array("si",&$fecha,&$seteo_turno) : 
          ($tipo_estudio ? array("si",&$fecha,&$tipo_estudio) : 
          ($especialidad ? array("si",&$fecha,&$especialidad) : array("s",&$fecha)));          
  if($medico) {
    $params[0] = count($params) == 0 ? "i" : $params[0] . "i";   
    $params[count($params)] = &$medico;    
  }
  if($consultorio) {
    $params[0] = count($params) == 0 ? "i" : $params[0] . "i";    
    $params[count($params)] = &$consultorio;    
  } else if ($sucursal) {
    $params[0] = count($params) == 0 ? "i" : $params[0] . "i";    
    $params[count($params)] = &$sucursal;    
  }
  if($dia_semana) {
    $params[0] = count($params) == 0 ? "s" : $params[0] . "s";    
    $params[count($params)] = &$dia_semana;             
  }
  if($paciente) {
    $params[0] = count($params) == 0 ? "i" : $params[0] . "i";    
    $params[count($params)] = &$paciente;       
  }  
  if($franja_horaria_inicio && $franja_horaria_fin) {
    $params[0] = count($params) == 0 ? "ss" : $params[0] . "ss";  
    $aux = "1900-01-01 " .$franja_horaria_inicio;
    $aux2 = "1900-01-01 " .$franja_horaria_fin;
    $params[count($params)] = &$aux;       
    $params[count($params)] = &$aux2;       
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
      $size = count($mydatos["datos"]);          
      if($size > $TURNOSBUSQUEDAMAXIMO) {
        $last_date = $mydatos["datos"][$size-1]["dia_agenda"];
        $last_i = $size;
        //print_r($last_date);
        for ($i=$size-1; $i >= 0 ; $i--) { 
          if($mydatos["datos"][$i]["dia_agenda"] < $last_date) {
            $last_i = $i;
            break;
          }
        }
        $datos = array_slice($mydatos["datos"], 0, $last_i);        
      } else{
        $datos = $mydatos["datos"];
      }
      //
      $id = -1;      
      $id_agendas = array();
      $agrupador_agenda = -1;
      $agendas_turnos = array();      
      $agendas = NULL;
      $turnos = NULL;
      $sobreturnos = NULL;
      
      foreach ($datos as $agenda) {        
        $id_agenda = $agenda["id_agenda"];        
        $id_agrupador_agenda = $agenda["idmedico"] . $agenda["idconsultorio"] . strtotime($agenda["dia_agenda"]);

        $sobreturnos = array(                               
          "id_paciente"  => $agenda["idpaciente"],
          "apellido_paciente"  => $agenda["apellidopaciente"] ?? null,
          "id_estado"  => $agenda["idestado"],
          "col"  => $agenda["col"],
          "id_tipo_cobertura"  => $agenda["idtipocobertura"],
          "id_iva_cobertura"  => $agenda["idivacobertura"],
          "facturar"  => $agenda["facturar"],
          "id_copago"  => $agenda["idcopago"],
          "id_posnet"  => $agenda["idposnet"],
          "importe"  => $agenda["importe"],
          "facturado"  => $agenda["facturado"]   
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
              $agendas[$agrupador_agenda]["turnos"][$id_agenda]["sobreturnos"][$agenda["id_turno"]] = $sobreturnos;
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