<?php
$respuesta = array();
$id_persona = nnreqcleanint("id_persona");
if ($id_persona) {
  $sql = "
    update 
      personas
    set 
      fechaaltaterminoscondiciones = CURDATE()
    where 
      id = ?
  ";
  $params = array(
    "i",
    &$id_persona
  );  
  $mydatos = my_query($sql, $params, false);
  if (($mydatos !== false) && ($mydatos["filas_afectadas"] == 1)) {
    $codigo = 0;
    $descripcion = "OK";   
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
