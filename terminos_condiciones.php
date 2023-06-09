<?php
$respuesta = array();

$tipo_texto = reqtrim("tipotexto");
if ($tipo_texto) {
  $sql = "
    select 
      terminosycondiciones 
    from 
      terminosycondiciones
    where 
      tipotexto = ?
      and inactivo = 0    
  ";
  $params = array(
    "s",
    &$tipo_texto
  );  
  $mydatos = my_query($sql, $params);
  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";
    if ($mydatos["datos"]) {
      $respuesta = array(        
        "terminosycondiciones" => $mydatos["datos"][0]["terminosycondiciones"]        
      );      
    } else {
      $respuesta = array(
        "terminosycondiciones" => NULL
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
