<?php
$respuesta = array();

$sql = "
select
  su.codigo       su_codigo,
  su.descripcion  su_descripcion,
  co.codigo       co_codigo,
  co.descripcion  co_descripcion
from
  sucursales su
  left outer join cal_consultorios co on co.idsucursal = su.codigo
order by
  su.codigo,
  co.codigo
";
$mydatos = my_query($sql, NULL);
if ($mydatos !== false) {
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"]) {
    $sucursales = array();
    $su_codigo = -1;
    $datos = array();
    $dat = NULL;
    foreach ($mydatos["datos"] as $sucursal_i) {
      $sucursal_codigo = $sucursal_i["su_codigo"];
      if ($sucursal_codigo != $su_codigo) {
        if ($dat) {
          $datos[$su_codigo] = $dat;
        }
        $su_codigo = $sucursal_codigo;
        $dat = array(
          "descripcion"  => $sucursal_i["su_descripcion"],
          "consultorios" => array(
            $sucursal_i["co_codigo"] => $sucursal_i["co_descripcion"]
          )
        );
      } else {
        $dat["consultorios"][$sucursal_i["co_codigo"]] = $sucursal_i["co_descripcion"];
      }
    }
    if ($dat) {
      $datos[$su_codigo] = $dat;
    }
    $respuesta = array(
      "sucursales" => $datos
    );
  } else {
    $respuesta = array(
      "sucursales" => NULL
    );
  }
} else {
  $codigo = -22;
  $descripcion = "Error en los datos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
