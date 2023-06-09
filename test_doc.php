<?php
$respuesta = array();

$documento = reqtrim("documento");
$nacionalidad = nnreqcleanint("nacionalidad");
global $TURNOSBONUSMAXIMO;
if ($documento && !is_null($nacionalidad)) {
  $sql = "
    select
      email,
      emailalt,
      clave,
      fechaconfirmado,
      turnosbonus
    from
      personas
    where
      dni = ?
    and idnacionalidad = ?
  ";
  $params = array(
    "si",
    &$documento,
    &$nacionalidad
  );  
  $mydatos = my_query($sql, $params);  
  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";
    if ($mydatos["datos"]) {
      $email = obfuscar_str($mydatos["datos"][0]["email"]);
      $email_alt = obfuscar_str($mydatos["datos"][0]["emailalt"]);      
      $turnos = $mydatos["datos"][0]["turnosbonus"] ?? 0;
      $estado = $mydatos["datos"][0]["clave"] ? $mydatos["datos"][0]["fechaconfirmado"] ? 4 : 3 : 2;
      $estado_txt = $mydatos["datos"][0]["clave"] ? $mydatos["datos"][0]["fechaconfirmado"] ? "registro confirmado" : "registro completo" : "registro incompleto";      
      if($estado == 4 || $turnos < $TURNOSBONUSMAXIMO) {
        $respuesta = array(
          "email" => array(
            $email,
            $email_alt
          ),
          "estado" => $estado,
          "estado_txt" => $estado_txt
        );
      }
      else{
        $codigo = -23;
        $descripcion = "Turnos disponibles superados. \n El usuario debe ser confirmado personalmente";
      }
    } else {
      $respuesta = array(
        "email" => array(
          NULL,
          NULL
        ),
        "estado" => 1,
        "estado_txt" => "no registrado"
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
