<?php
$respuesta = array();

$sql = "
select
  codigo,
  descripcion,
  idtipoevaluacion,
  indicacionespacientes,
  porcmedseguimiento
from
  cal_tiposdeestudios  
order by
  codigo
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
$mydatos = my_query($sql, NULL);
$mydatos2 = my_query($sql2, NULL);
$mydatos3 = my_query($sql3, NULL);
$mydatos4 = my_query($sql4, NULL);
$mydatos8 = my_query($sql8, NULL);

if ($mydatos !== false && $mydatos2 !== false) {
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"] && $mydatos2["datos"] && $mydatos3["datos"] && $mydatos4["datos"] && $mydatos8["datos"]) {
    $id;    
    $datos = array();  
    $datos2 = array();   
    $datos3 = array();    
    $datos4 = array();    
    $datos8 = array();    
    foreach ($mydatos["datos"] as $dato) {
      $id = $dato["codigo"];
      $datos[$id] = array_slice($dato,1);        
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
      0 => array("descripción" => "Primaria"),
      1 => array("descripción" => "Secundaria"),
      2 => array("descripción" => "Terciaria"),
      3 => array("descripción" => "Universitaria")      
    );
    $datos6 = array(
      0 => array("descripción" => "Soltero/a"),
      1 => array("descripción" => "Casado/a"),
      2 => array("descripción" => "Viudo/a"),
      3 => array("descripción" => "Divorciado/a")      
    );
    $datos7 = array(
      0 => array("descripción" => "Médico"),
      1 => array("descripción" => "Familia o Amigo"),
      2 => array("descripción" => "Cobertura Médica"),
      3 => array("descripción" => "Otro")      
    );
    foreach ($mydatos8["datos"] as $dato8) {
      $id = $dato8["codigo"];
      $datos8[$id] = array_slice($dato8,1);        
    }
    $respuesta = array(
      "tipos_estudio" => $datos,
      "especialidades" => $datos2,
      "agendas_medicos" => $datos3,
      "turnos_estado" => $datos4,
      "educacion" => $datos5,
      "estado_civil" => $datos6,
      "quien_deriva" => $datos7,
      "formas_pago" => $datos8,
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
