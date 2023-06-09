<?php
$respuesta = array();

$sql = "
select 
  tde.codigo,   
  tde.descripcion,
  tde.idtipoevaluacion,
  tde.indicacionespacientes,
  tde.porcmedseguimiento,
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
  tde.codigo,   
  tde.descripcion,
  tde.idtipoevaluacion,
  tde.indicacionespacientes,
  tde.porcmedseguimiento,
  tde.agrupador,
  tde.tipo,
  tde.web,
  v1.idmedico,
  usr.nombre
from 
  cal_tiposdeestudios tde 
inner join 
  (select 
    tde1.codigo,
    sem1.idmedico
    from 
      cal_tiposdeestudios tde1 
    inner 
      join cal_seteosestudiosmedicos sem1 
    on tde1.codigo = sem1.idtipodeestudio
    group by tde1.codigo, sem1.idmedico) v1
on v1.codigo = tde.codigo
inner join usuarios usr
  on usr.id = v1.idmedico    
order by tde.codigo desc;
";

/*
otros campos
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

$mydatos = my_query($sql, NULL);
$mydatos2 = my_query($sql2, NULL);
$mydatos3 = my_query($sql3, NULL);
$mydatos4 = my_query($sql4, NULL);

if ($mydatos !== false && $mydatos2 !== false) {
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"] && $mydatos2["datos"] && $mydatos3["datos"] && $mydatos4["datos"]) {
    $id;    
    $datos = array();  
    $datos2 = array();   
    $datos3 = array();    
    $datos4 = array();    
    
   /* foreach ($mydatos["datos"] as $dato) {
      $id = $dato["codigo"];
      $datos[$id] = array_slice($dato,1);        
    }*/

    $id_est = -1;
    $id_agenda = -1;
    $datos = $mydatos["datos"];
    foreach ($datos as $dat) {        
      
      $id_estudio = $dat["codigo"];        
      $id_agenda = $dat["idmedico"];
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
          "agrupador"  => $dat["agrupador"],
          "tipo"  => $dat["tipo"],
          "web"  => $dat["web"],
          "agendas" => array(
            $dat["idmedico"] => $agenda
            )
        );
      } else {        
          $estudios[$id_estudio]["agendas"][$id_agenda] = $agenda;
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
    $respuesta = array(
      "tipos_estudio" => $estudios,
      "especialidades" => $datos2,
      "dats_medicos" => $datos3,
      "turnos_estado" => $datos4
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
