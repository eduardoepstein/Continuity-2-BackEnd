<?php
$respuesta = array();

$sql = "
select
  id,
  pais,
  iso
from
  paises  
order by
  id
";
$mydatos = my_query($sql, NULL);
if ($mydatos !== false) {
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"]) {
    $id;
    $iso_paises = array();
    $datos = array();
    foreach ($mydatos["datos"] as $pais) {
        $id = $pais["id"];
        $iso_paises = array(
            "descripcion"  => $pais["pais"],
            "iso" => $pais["iso"]
          );
        if ($iso_paises) {
            $datos[$id] = $iso_paises;
        }
    }
    $respuesta = array(
      "paises" => $datos
    );
  } else {
    $respuesta = array(
      "paises" => NULL
    );
  }
} else {
  $codigo = -22;
  $descripcion = "Error en los datos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
