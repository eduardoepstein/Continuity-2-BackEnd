<?php
/*
Copyright Kost
Licensed to: Continuity of Care
Prohibida distribucion y uso sin permiso explicito
*/

require_once("../cfg.php");

define("RES_EXITO", 0);
define("RESMSG_EXITO", "OK");

//header_remove("X-Powered-By");
header("Expires: Mon, 1 Feb 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

function log_php_kost($s, $min_debug = 0) {
  global $KOST_DEBUG, $LOG_PHP_KOST_PREFIX;
  if (!empty($KOST_DEBUG) && ($KOST_DEBUG >= $min_debug)) {
    error_log("$LOG_PHP_KOST_PREFIX$s");
  }
}

function formatear_exception($e) {
  $trace = $e->getTrace();
  $msg = $e->getMessage();
  $code = $e->getCode();
  if (method_exists($e, "getSeverity")) {
    $severity = $e->getSeverity();
  } else {
    $severity = "*";
  }
  $mensaje = "-------- EXCEPCION --------\n$code / $severity; $msg\n--------------------------------\n";
  foreach ($trace as $paso) {
    if (isset($paso["line"])) {
      foreach ($paso as $indice=>$valor) {
        if (($indice == "file") || ($indice == "line")) {
          $mensaje .= "$indice: $valor\n";
        }
      }
      $mensaje .= "--------\n";
    }
  }
  return($mensaje);
}

set_exception_handler(function($e){log_php_kost(formatear_exception($e));});
set_error_handler(function($errno, $errstr, $errfile, $errline){throw new ErrorException($errstr, $errno, 1, $errfile, $errline);});

function nreq($s) {
  return (isset($_REQUEST[$s]) ? str_replace("\r", "\n", str_replace("\r\n", "\n" , $_REQUEST[$s])) : NULL);
}

function reqtrim($s) {
  return (isset($_REQUEST[$s]) ? str_replace("\r", "\n", str_replace("\r\n", "\n" , trim($_REQUEST[$s]))) : "");
}

function nreqtrim($s) {
  return (isset($_REQUEST[$s]) ? str_replace("\r", "\n", str_replace("\r\n", "\n" , trim($_REQUEST[$s]))) : NULL);
}

function nnreqtrim($s) {
  return ((isset($_REQUEST[$s]) && (trim($_REQUEST[$s]) !== "")) ? str_replace("\r", "\n", str_replace("\r\n", "\n" , trim($_REQUEST[$s]))) : NULL);
}

function reqcleanint($s) {
  return ((isset($_REQUEST[$s]) && (trim($_REQUEST[$s]) !== "")) ? intval(trim($_REQUEST[$s])) : 0);
}

function nnreqcleanint($s) {
  return ((isset($_REQUEST[$s]) && (trim($_REQUEST[$s]) !== "")) ? intval(trim($_REQUEST[$s])) : NULL);
}

function reqcleanreal($s) {
  return ((isset($_REQUEST[$s]) && (trim($_REQUEST[$s]) !== "")) ? floatval(trim($_REQUEST[$s])) : 0);
}

function nnreqcleanreal($s) {
  return ((isset($_REQUEST[$s]) && (trim($_REQUEST[$s]) !== "")) ? floatval(trim($_REQUEST[$s])) : NULL);
}

function nreqcleanarray($s) {  
  return (isset($_REQUEST[$s]) ? preg_split('{,}',$_REQUEST[$s],-1,PREG_SPLIT_NO_EMPTY) : NULL);
}

function enviar_http_500($s, $e = NULL) {
  global $funcion;

  $s0 = $funcion ? "#$funcion " : "";
  $s = "500 Internal Server Error $s0$s";
  header("{$_SERVER["SERVER_PROTOCOL"]} $s", true, 500);
  header("Content-Type: text/plain; charset=utf-8");
  echo "$s\n";
  log_php_kost($s);
  if (is_object($e)) {
    log_php_kost(PHP_EOL . formatear_exception($e));
  }
  die;
}

function randomstring($len = NULL) {
  global $MINRANDLEN, $MAXRANDLEN;
  if (!$len) {
    $len = rand($MINRANDLEN, $MAXRANDLEN);
  }
  return (bin2hex(openssl_random_pseudo_bytes($len)));
}

function corregir_utf8($s) {
  return (mb_convert_encoding($s, "UTF-8", "UTF-8, ISO-8859-1, ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6, ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-11, ISO-8859-12, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16, CP1251, CP1252, USC-2, ASCII"));
}

function enviar_respuesta($s, $es_json = true) {
  header("Access-Control-Allow-Origin: *");
  if ($es_json) {
    header("Content-Type: application/json; charset=utf-8");
  } else {
    header("Content-Type: text/plain; charset=utf-8");
  }
  log_php_kost($s, 3);
  echo $s;
}

function enviar_respuesta_datos($codigo, $descripcion = "", $respuesta = NULL) {
  $arr_result = array(
    "resultado" => array(
      "codigo" => $codigo,
      "descripcion" => corregir_utf8($descripcion),
      "NOBREACH" => randomstring()
    ),
    "respuesta" => $respuesta
  );
  enviar_respuesta(json_encode($arr_result));
  die;
}

function my_conectar() {
  global $mylink;
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  $mylink = mysqli_connect(DBHOST, DBUSUARIO, DBCLAVE, DBNOMBRE);
}

function my_query($sql, $params = NULL, $devolver_datos = true) {
  global $mylink;
  $res = false;
  $datos = NULL;
  if ($mylink && ($stmt = mysqli_prepare($mylink, $sql))) {
    if ($params) {
      call_user_func_array("mysqli_stmt_bind_param", array_merge(array(&$stmt), $params));
    }
    mysqli_stmt_execute($stmt);
    if ($devolver_datos) {
      $result_stmt = mysqli_stmt_get_result($stmt);
      $datos = mysqli_fetch_all($result_stmt, MYSQLI_ASSOC);
    }
    $insert_id = mysqli_stmt_insert_id($stmt);
    $filas_afectadas = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    $res = array("datos" => $datos, "insert_id" => $insert_id, "filas_afectadas" => $filas_afectadas);
  }
  return $res;
}

function validar_acceso($perfil_requerido = "") {
  global $mylink, $funcion;
  $perfil_requerido = trim($perfil_requerido);
  if (!($perfil_requerido && $mylink && $funcion)) {
    enviar_http_500("-100");
  }
}

function obfuscar_str($s) {
  global $OBFUSCAR_LEN;
  if (is_null($s)) {
    return NULL;
  } elseif ($s === "") {
    return ($s);
  } else {
    $l = strlen($s);
    return (substr($s, 0, $OBFUSCAR_LEN) . "*****");
  }
}

function comprobar_version_actualizada() {
  global $VERSIONMINIMA, $appver;
  $vmin = explode(".", $VERSIONMINIMA);
  $vapp = explode(".", $appver);
  if (
    (count($vapp) == 3)
    && (
      (intval($vapp[0]) > intval($vmin[0]))
      || ((intval($vapp[0]) == intval($vmin[0])) && (intval($vapp[1]) > intval($vmin[1])))
      || ((intval($vapp[0]) == intval($vmin[0])) && (intval($vapp[1]) == intval($vmin[1])) && (intval($vapp[2]) >= intval($vmin[2])))
    )
  ) {
    return (true);
  }
  return (false);
}

$codigo = -1;
$descripcion = "Error no detallado";

$version_ok = NULL;
$mylink = NULL;

try {
  if ($funcion = strtolower(reqtrim("funcion"))) {
    if (preg_match("/^\w+$/", $funcion) !== 1) {
      enviar_http_500("intento de abuso logueado y denunciado -102");
    } else {
      $appver = reqtrim("appver");
      if ($EXIGIRVERSION && !comprobar_version_actualizada()) {
        enviar_respuesta_datos(-2, "Version desactualizada");
      }
      session_start();
      if ($_SESSION) {
        session_regenerate_id(true);
      }
    }
  } else {
    enviar_http_500("-101");
  }

  $qs = preg_replace("/^funcion=[^&]*&?/i", "", $_SERVER['QUERY_STRING']);

  $post_data = trim(file_get_contents('php://input'));
  if ($post_data) {
    //$post_data = PHP_EOL . $post_data;
    $post_data = " - POST: " . $post_data;
  }
  log_php_kost("/$funcion?$qs$post_data", 1);
  log_php_kost("DECODED: /$funcion?" . urldecode($qs . $post_data) . "", 2);

  $servicio_php = "servicios/$funcion.php";
  if (file_exists("$servicio_php")) {
    my_conectar();
    include($servicio_php);
  } else {
    enviar_http_500("solicitud invalida -103");
  }
} catch (mysqli_sql_exception $e) {
  log_php_kost(formatear_exception($e) . " db:{$e->getLine()}");
  enviar_http_500($SQL_DESARROLLO ? " db:{$e->getLine()}:{$e->getMessage()}, -104" : "-104");
} catch (Exception $e) {
  log_php_kost(formatear_exception($e) . " general:{$e->getLine()}");
  enviar_http_500($SQL_DESARROLLO ? " general:{$e->getLine()}:{$e->getMessage()}, -105" : "-105");
}

//ñ
