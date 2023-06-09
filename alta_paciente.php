<?php
$respuesta = array();
$documento = reqtrim("lg_dni");
$nacionalidad = nnreqcleanint("lg_id_nacionalidad");
$apellido = reqtrim("usr_apellido");
$apellido_otro = reqtrim("usr_apellido_otro");
$nombre = reqtrim("usr_nombre");
$nombre_otro = reqtrim("usr_nombre_otro");
$fecha_nacimiento = reqtrim("usr_fecnacimiento");
$sexo = nnreqcleanint("usr_sexo");
$genero = nnreqcleanint("usr_genero");
$domicilio = reqtrim("usr_domicilio");
$cod_postal = reqtrim("usr_codpostal");
$localidad = reqtrim("usr_localidad");
$partido = reqtrim("usr_partido");
$provincia = reqtrim("usr_provincia");
$pais_domicilio = nnreqcleanint("usr_id_pais_domicilio");
$email = reqtrim("usr_email");
$email_alternativo = reqtrim("usr_email_alt");
$telefono_principal = reqtrim("usr_telefono_principal");
$principal_celular = nnreqcleanint("usr_telefono_principal_escelular") ?? 1;
$telefono_secundario = reqtrim("usr_telefono_secundario");
$secundario_celular = nnreqcleanint("usr_telefono_secundario_escelular") ?? 1;
$alto_riesgo = nnreqcleanint("pa_altoriesgo") ?? 0;
$vip = nnreqcleanint("pa_vip") ?? 0;
$comentarios = reqtrim("pa_comentarios");
$fecha_proceso = reqtrim("fecha_proceso");
$fecha_modificacion = reqtrim("lg_fecha_modificacion");
$usuario_modificacion = nnreqcleanint("lg_usuario_modificacion");
$iou;

if ($documento&&!(is_null($nacionalidad)) && $apellido && $nombre && $fecha_nacimiento && 
    !(is_null($sexo)) && $domicilio && $cod_postal && $provincia && !(is_null($pais_domicilio)) && 
    $email && $telefono_principal) {
  $sql = "
  select
    id
  from
    personas
  where
    dni = ?
    and idnacionalidad = ?
";
$params = array(
  "si",
  &$documento,&$nacionalidad
);
$mydatos = my_query($sql, $params);
if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";
  if ($mydatos["datos"]) {    
    $iou = $mydatos["datos"][0]["id"];    
    $sql = "
      update
        personas
      set
        apellido = ?,
        apellidootro = ?,
        nombre = ?,
        nombreotro = ?,
        fecnacimiento = ?,
        sexo = ?,
        genero = ?,
        domicilio = ?,
        codpostal = ?,
        localidad = ?,
        paisdomicilio = ?,
        partido = ?,
        provincia = ?,
        email = ?,
        emailalt = ?,
        telefonoprincipal = ?,
        telefonosecundario = ?,
        telefonoprincipalescelular = ?,
        telefonosecundarioescelular = ?,
        altoriesgo = ?,
        vip = ?,
        comentarios = ?,
        usuariomodificacion = ?,
        fechamodificacion = SYSDATE()
      where
        id = ?        
    ";
    $params = array(
      "sssssiissssssssssiiiisii",                  
      &$apellido,&$apellido_otro,&$nombre,&$nombre_otro,
      &$fecha_nacimiento,&$sexo,&$genero,
      &$domicilio,&$cod_postal,&$localidad,&$pais_domicilio,&$partido,&$provincia,
      &$email,&$email_alternativo,
      &$telefono_principal,&$telefono_secundario,&$principal_celular,&$secundario_celular,
      &$alto_riesgo,&$vip,&$comentarios,
      &$usuario_modificacion,
      &$iou
    );    
  } else {    
    $sql = "
      insert into
        personas(
          dni,idnacionalidad,
          apellido,apellidootro,nombre,nombreotro,
          fecnacimiento,sexo,genero,
          domicilio,codpostal,localidad,paisdomicilio,partido,provincia,
          email,emailalt,
          telefonoprincipal,telefonosecundario,telefonoprincipalescelular,telefonosecundarioescelular,
          altoriesgo,vip,comentarios,
          usuariomodificacion,fechaalta
        )
      values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,SYSDATE()
      )        
    ";
    $params = array(
      "sisssssiissssssssssiiiisi",
      &$documento,&$nacionalidad,
      &$apellido,&$apellido_otro,&$nombre,&$nombre_otro,
      &$fecha_nacimiento,&$sexo,&$genero,
      &$domicilio,&$cod_postal,&$localidad,&$pais_domicilio,&$partido,&$provincia,
      &$email,&$email_alternativo,
      &$telefono_principal,&$telefono_secundario,&$principal_celular,&$secundario_celular,
      &$alto_riesgo,&$vip,&$comentarios,
      &$usuario_modificacion      
    );    
  }
  $mydatos2 = my_query($sql,$params,false);
  if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {
    $iou = $mydatos["datos"][0]["id"] ?? $mydatos2["insert_id"] ?? -1;   
    $respuesta = array(
      "id" => $iou
    );
  } else {
    $codigo = -23;
    $descripcion = "Error en los datos";
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
