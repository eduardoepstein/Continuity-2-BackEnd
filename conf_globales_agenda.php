<?php
$respuesta = array();

$sql = "
select 
  tde.codigo,   
  tde.descripcion,
  tde.idtipoevaluacion,
  tde.indicacionespacientes,
  tde.porcmedseguimiento,
  tde.pantalla,
  tde.tipo,
  sem.idmedico,
  usr.nombre
from 
  cal_tiposdeestudios tde 
inner join 
  cal_seteosestudiosmedicos sem
  on tde.codigo = sem.idtipodeestudio  
inner join usuarios usr
  on usr.id = sem.idmedico    
group by tde.codigo,   
  tde.descripcion,
  tde.idtipoevaluacion,
  tde.indicacionespacientes,
  tde.porcmedseguimiento,
  sem.idmedico,
  usr.nombre
order by tde.codigo desc;
";

$sql = "
select 
  tds.codigo,   
  tds.descripcion,
  tds.idtipoevaluacion,
  tds.indicacionespacientes,
  tds.porcmedseguimiento,
  tds.web,
  tds.pantalla,
  tds.agrupador,
  tds.tipo,
  usr.id as idmedico,
  usr.nombre,
  stt.idconsultorio,
  cns.idsucursal
from cal_tiposdeestudios tds 
inner join cal_seteosestudiosmedicos stm 
	on stm.idtipodeestudio = tds.codigo 
inner join cal_seteosturnos stt 
	on (stt.id = stm.idseteoturno and stt.idmedico = stm.idmedico and stt.activo = 1)
inner join cal_consultorios cns 
	on cns.codigo = stt.idconsultorio
inner join usuarios usr
  	on usr.ID = stt.idmedico
order by tds.codigo desc;
";
/* otros campos
  web,
  mostraragtec,
  tipo,  
  pantalla,
  comisionlezica
*/
$sql2 = "
select
  codigo,
  descripcion,
  tipo
from
  cal_especialidades
order by
  codigo
";

$sql3 = "
select
  distinct(usr.id),
  usr.nombre  
from
  cal_seteosturnos sst
inner join usuarios usr
  on usr.id = sst.idmedico
order by
  nombre desc
";

$sql4 = "
select
  codigo,
  descripcion,
  mostrar,
  idproximoestado
from
  cal_estados  
order by
  codigo desc
";

$sql8 = "
select 
  codigo,
  descripcion
from
  caja_formasdepago
order by 
  codigo desc
";

$sql12 = "
select 
  codigo,
  descripcion
from
  perfiles
order by 
  codigo desc
";

$sql13 = "
select 
  codigo,
  descripcion
from
  unidades
order by 
  codigo desc
";

$mydatos = my_query($sql, NULL);
$mydatos2 = my_query($sql2, NULL);
$mydatos3 = my_query($sql3, NULL);
$mydatos4 = my_query($sql4, NULL);
$mydatos8 = my_query($sql8, NULL);
$mydatos12 = my_query($sql12, NULL);
$mydatos13 = my_query($sql13, NULL);

if ($mydatos !== false && $mydatos2 !== false) {
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"] && $mydatos2["datos"] && $mydatos3["datos"] && $mydatos4["datos"] && $mydatos8["datos"]&& $mydatos12["datos"]&& $mydatos13["datos"]) {
    $id;    
    $datos = array();  
    $datos2 = array();   
    $datos3 = array();    
    $datos4 = array();    
    $datos8 = array();    
    $datos12 = array();    
    $datos13 = array();    
    $id_est = -1;
    $id_agenda = -1;
    $datos = $mydatos["datos"];
    foreach ($datos as $dat) {        
      
      $id_estudio = $dat["codigo"];        
      $id_agenda = $dat["idmedico"];
      $id_sucursal = $dat["idsucursal"];
      $agenda = array(
        "descripcion"  => $dat["nombre"]
      );

      if($id_estudio != $id_est) {
        $id_est = $id_estudio;

        $estudios[$id_estudio] = array(
          "descripcion"  => $dat["descripcion"],
          "id_tipo_evaluacion"  => $dat["idtipoevaluacion"],                     
          "indicaciones_pacientes"  => $dat["indicacionespacientes"],
          "porc_med_seguimiento"  => $dat["porcmedseguimiento"],
          "web"  => $dat["web"],
          "pantalla"  => $dat["pantalla"],
          "tipo"  => $dat["tipo"],
          "agrupador"  => $dat["agrupador"],
          "agendas_medicos" => array(
            $dat["idmedico"] => $agenda
          ),
          "sucursales" => array($dat["idsucursal"])
        );
      } else {        
          $estudios[$id_estudio]["agendas_medicos"][$id_agenda] = $agenda;
          if(!in_array($id_sucursal, $estudios[$id_estudio]["sucursales"])){
            $estudios[$id_estudio]["sucursales"][] = $id_sucursal;
          }          
      }        
    }

    foreach ($mydatos2["datos"] as $dato2) {
      $id = $dato2["codigo"];
      $datos2[$id] = array_slice($dato2,1);        
    }
    foreach ($mydatos3["datos"] as $dato3) {
      $id = $dato3["id"];
      $datos3[$id] = array("nombre" => $dato3["nombre"]);        
    }
    foreach ($mydatos4["datos"] as $dato4) {
      $id = $dato4["codigo"];
      $datos4[$id] = array_slice($dato4,1);        
    }
    $datos5 = array(
      "1" => array("descripción" => "Secundaria"),
      "0" => array("descripción" => "Primaria"),      
      "2" => array("descripción" => "Terciaria"),
      "3" => array("descripción" => "Universitaria")      
    );
    $datos6 = array(
      "0" => array("descripción" => "Soltero/a"),
      "1" => array("descripción" => "Casado/a"),
      "3" => array("descripción" => "Divorciado/a"),  
      "2" => array("descripción" => "Viudo/a") 
    );
    $datos7 = array(
      "0" => array("descripción" => "Médico"),
      "2" => array("descripción" => "Cobertura Médica"),
      "1" => array("descripción" => "Familia o Amigo"),      
      "3" => array("descripción" => "Otro")      
    );    
    foreach ($mydatos8["datos"] as $dato8) {
      $id = $dato8["codigo"];
      $datos8[$id] = array_slice($dato8,1);        
    }
    $datos9 = array(
      "-1" => array("descripción" => "Otros"),
      "0" => array("descripción" => "Adulto"),
      "1" => array("descripción" => "Pediátrico"),      
      "2" => array("descripción" => "Ambos")      
    );
    $datos10 = array(
      "-1" => array("descripción" => "Otros estudios"),
      "0" => array("descripción" => "Estudios"),
      "1" => array("descripción" => "Consultas"),      
      "2" => array("descripción" => "Rehablitación"),
      "3" => array("descripción" => "Evaluaciones")
    );
    $datos11 = array(
      "1" => array("descripción" => "Agenda paciente"),
      "0" => array("descripción" => "Agenda administrativo")
    );        
    foreach ($mydatos12["datos"] as $dato12) {
      $id = $dato12["codigo"];
      $datos12[$id] = array_slice($dato12,1);        
    }
    foreach ($mydatos13["datos"] as $dato13) {
      $id = $dato13["codigo"];
      $datos13[$id] = array_slice($dato13,1);        
    }
    $datos14 = array(      
      "0" => array("descripción" => "OS/PREPAGA"),
      "1" => array("descripción" => "PARTICULAR"),
      "2" => array("descripción" => "PLAN BENEFICIOS"),
      "3" => array("descripción" => "EXCEPCIÓN")
    );    
    $datos15 = array(
      
      "0" => array("descripción" => "Sin Copago"),
      "1" => array("descripción" => "Copago Fijo"),
      "2" => array("descripción" => "Copago por %"),
      "3" => array("descripción" => "Ver POSNET")
    );    
    $datos16 = array(
      "-1" => array("descripción" => "No completado"),
      "0" => array("descripción" => "Médico"),
      "1" => array("descripción" => "Cobertura Salud"),
      "2" => array("descripción" => "Otro")
    );    
    $datos17 = array(
      "-1" => array("descripción" => "Otro"),
      "0" => array("descripción" => "Ante evento"),
      "1" => array("descripción" => "Única vez"),  
      "4" => array("descripción" => "Cada 4 hs"),
      "8" => array("descripción" => "Cada 8 hs"),
      "12" => array("descripción" => "Cada 12 hs"),
      "24" => array("descripción" => "Cada 24 hs"),          
      "168" => array("descripción" => "Semana"),
      "720" => array("descripción" => "Mes")
    );   
    $datos18 = array(
      "1" => array("descripción" => "Agudo"),
      "0" => array("descripción" => "Crónico")
    );   
    $respuesta = array(
      "tipos_estudio" => $estudios,
      "especialidades" => $datos2,
      "agendas_medicos" => $datos3,
      "turnos_estado" => $datos4,
      "educacion" => $datos5,
      "estado_civil" => $datos6,
      "quien_deriva" => $datos7,
      "formas_pago" => $datos8,
      "tipos_paciente" => $datos9,
      "tipos_practica" => $datos10,
      "tipos_agenda" => $datos11,
      "perfiles" => $datos12,
      "unidades" => $datos13,
      "tipos_coberturas" => $datos14,
      "copago" => $datos15,
      "quien_envia" => $datos16,
      "frecuencia_medicacion" => $datos17,
      "tratamiento" => $datos18
    );
  } else {
    $respuesta = array();
  }
} else {
  $codigo = -22;
  $descripcion = "Error en los datos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
