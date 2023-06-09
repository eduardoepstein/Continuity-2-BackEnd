<?php
$respuesta = array();

$sql = "
select
  co.codigo       co_codigo,
  co.descripcion  co_descripcion,
  pl.id           pl_id,
  pl.descripcion  pl_descripcion
from
  coberturassalud co
  left outer join planes pl on pl.idcobertura = co.codigo and pl.baja = 0
where
  co.baja = 0
order by
  co.codigo,
  pl.id
";
$mydatos = my_query($sql, NULL);
if ($mydatos !== false) {
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"]) {
    $coberturas = array();
    $id_cobertura = -1;
    $datos = array();
    $dat = NULL;
    foreach ($mydatos["datos"] as $cob) {
      $id_cob = $cob["co_codigo"];
      if ($id_cob != $id_cobertura) {
        if ($dat) {
          $datos[$id_cobertura] = $dat;
        }
        $id_cobertura = $id_cob;
        $dat = array(
          "descripcion"  => $cob["co_descripcion"],
          "planes" => array(
            $cob["pl_id"] => $cob["pl_descripcion"]
          )
        );
      } else {
        $dat["planes"][$cob["pl_id"]] = $cob["pl_descripcion"];
      }
    }
    if ($dat) {
      $datos[$id_cobertura] = $dat;
    }
    $respuesta = array(
      "coberturas" => $datos
    );
  } else {
    $respuesta = array(
      "coberturas" => NULL
    );
  }
} else {
  $codigo = -22;
  $descripcion = "Error en los datos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
