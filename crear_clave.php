<?php
$documento = reqtrim("documento");
$pos_email = nnreqcleanint("pos_email");
$nacionalidad = nnreqcleanint("nacionalidad");
if ($documento && !is_null($pos_email) && !is_null($nacionalidad)) {
  //agregar si campo cantidad de turno
  $sql = "
    select
      email,
      emailalt
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
    if ($mydatos["datos"]) {
      $email = "";
      if (($pos_email == 0) && !empty($mydatos["datos"][0]["email"])) {
        $email = $mydatos["datos"][0]["email"];
      } elseif (($pos_email == 1) && !empty($mydatos["datos"][0]["email_alt"])) {
        $email = $mydatos["datos"][0]["email_alt"];
      }
      if ($email) {
        $codigoemitido = time();
        $codigosecreto = bin2hex(openssl_random_pseudo_bytes($CODIGOLEN));
        $sql = "
          update
            personas
          set
            codigosecreto = ?,
            codigoemitido = ?
          where
            dni = ?
            and idnacionalidad = ?  
        ";
        $params = array(
          "sisi",
          &$codigosecreto,
          &$codigoemitido,
          &$documento,&$nacionalidad
        );
        $mydatos = my_query($sql, $params, false);
        if (($mydatos !== false) && ($mydatos["filas_afectadas"] == 1)) {
          $headers = "From: $NOREPLY\nReply-To: $NOREPLY\nMIME-Version: 1.0\nContent-Transfer-Encoding: 8bit\nContent-Type: text/plain; charset=\"UTF-8\"\n";
          $mensaje =
"Lezica - Cambio de clave.

Este mail está destinado exclusivamente a $email.
Si no es la persona indicada, por favor eliminelo
y avise a nuestro Soporte - $CORREOSOPORTE

Ud recibe este mensaje porque solicitó la creacion o
el cambio de su clave de entrada al servicio de Lezica.

Para restablever su clave, entre al siguiente enlace:

$ENDPOINTCLAVE/?documento=$documento&codigo=$codigosecreto

Este enlace será válido durante un plazo limitado.
En el caso de que al momento de entrar el enlace ya
se expiró, solicite su envio nuevamente.

Por favor no responda a este mail, es un mensaje
generado automaticamente por nuestro sistema de
autenticacion y esta direccion no se atiende por un
ser humano. Cualquier mail enviado a esta dirección
se descarta sin llegar a nadie.

En el caso de necesitar asistencia, le rogamos
comunicarse con nuestro Soporte mediante correo
$CORREOSOPORTE

Soporte.
";

          if (mail($email, "Cambio de clave - Lezica", $mensaje, $headers)) {
            $codigo = 0;
            $descripcion = "Email fue enviado a su direccion " . obfuscar_str($email);
          } else {
            $codigo = -26;
            $descripcion = "Error enviando email";
          }
        } else {
          $codigo = -25;
          $descripcion = "Error en los datos";
        }
      } else {
        $codigo = -24;
        $descripcion = "Falta email valido";
      }
    } else {
      $codigo = -23;
      $descripcion = "Documento invalido";
    }
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
