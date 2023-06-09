<?php
$documento = reqtrim("documento");
$nacionalidad = nnreqcleanint("nacionalidad");
$email = reqtrim("email");
if ($documento && !is_null($email) && !is_null($nacionalidad)) {
    $sql = "
    update
      personas
    set
      email = ?,
      fechaconfirmado = NULL,
      clave = NULL
    where
      dni = ?
      and idnacionalidad = ?  
      and email is NULL
      and emailalt is NULL  
  ";
  $params = array(
    "ssi",
    &$email,
    &$documento,&$nacionalidad
  );
  $mydatos = my_query($sql, $params, false);
  if (($mydatos !== false) && ($mydatos["filas_afectadas"] == 1)) {
    $codigo = 0;
    $descripcion = "Se guarda la direccion " . obfuscar_str($email);
  } else {
    $codigo = -22;
    $descripcion = "Error en los datos";
  }
} else {
    $codigo = -21;
    $descripcion = "Faltan argumentos requeridos";
}

enviar_respuesta_datos($codigo, $descripcion);

//ñ
