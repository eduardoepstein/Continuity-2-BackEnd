<?php
//parametros de ingreso

$entero = nnreqcleanint("parametro_int"); 
$texto = nnreqtrim("parametro_string");
/*estas funciones recupera el valor de la clave (parametro_int o parametro_string) almacenada en el direccionario $_REQUEST
$valor = $_REQUEST["parametro_int"]
$_REQUEST es una variable reservada que almacena el query string
*/

//inicio de variables de respuesta

$respuesta = array();

//armado de query
//para las consultas que no dependan de los parametros de ingreso creamos directamente la consulta como un string

$sql = "select codigo, descripcion from medicamentos"
$mydatos = my_query($sql, null);
//para las consultas que dependan de los parametros ingresados se los pasamos como prepared statement(asociarle el ?)
//luego hago el binding de los parametros, asociando las variables con sus respectivos ?
//para enteros 
$sql = "select codigo, descripcion from medicamentos where id=?"
$params = $entero ? array("i",&$entero) : null;
//para strings 
$sql = "select codigo, descripcion from medicamentos where descripcion=?"
$params = $texto ? array("s",&$texto) : null;

//y con esto hacemos la consulta

$mydatos = my_query($sql, $params);

//manejo de datos

if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";
    $datos = array();
    if ($mydatos["datos"]) {   
          
      foreach ($mydatos["datos"] as $dato) {        
        //proceso lso datos recibidos en mydatos y genero la respuesta $datos
      }  
      $respuesta = array(
        "datos" => $datos
      );
    } else {
      $respuesta = array(
        "datos" => NULL
      );
    }
  } else {
    $codigo = -22;
    $descripcion = "Error en los datos";
  }
//envio de respuestas

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
