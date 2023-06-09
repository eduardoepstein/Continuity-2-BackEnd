<?php
$respuesta = array();
$id_cie10 = nnreqcleanint("id");
$codigo_cie10 = nnreqcleanint("codigo");
$descripcion_cie10 = nreqtrim("descripcion") ? '%'.nreqtrim("descripcion").'%' : null;
$id_capitulo = nnreqcleanint("id_capitulo");
$id_subcapitulo = nnreqcleanint("id_subcapitulo");

if($id_cie10 || $codigo_cie10 ||$descripcion_cie10){
  
  $where = $id_cie10 ? " where cie.id = ? " : ( 
    $codigo_cie10 ? " where cie.codigo = ? ": ( 
      $descripcion_cie10 ? " where cie.descripcion like ? " : ( 
        $id_capitulo ? " where cap.codigo = ? " : (
          $id_subcapitulo ? " where sub.codigo = ? " : null))));

  $sql = "
    select
      cie.id,
      cie.codigo,
      cie.descripcion,        
      sub.codigo as sub_codigo,        
      cap.codigo as cap_codigo
    from cie10 cie
      left outer join cie10_subcapitulos sub
        on sub.id = cie.idsubcapitulo
      left outer join cie10_capitulos cap
        on cap.id = sub.idcapitulo
    {$where}      
    order by cie.descripcion asc
  ";

  $params = $id_cie10 ? array("i",&$id_cie10) : ( 
    $codigo_cie10 ? array("i",&$codigo_cie10) : ( 
      $descripcion_cie10 ? array("s",&$descripcion_cie10) : ( 
        $id_capitulo ? array("i",&$id_capitulo) : (
          $id_subcapitulo ? array("i",&$id_subcapitulo) : array()))));

  $mydatos = my_query($sql, $params);

  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";
    $datos = array();
    if ($mydatos["datos"]) {   
      $cie10 = array();    
      $id_cie10 = -1;
      $id_capitulo = -1;
      $id_subcapitulo = -1;    
      foreach ($mydatos["datos"] as $dato) {
        $id = $dato["id"];
        $id_cap = $dato["cap_codigo"];
        $id_sub = $dato["sub_codigo"];            
        $cie10[$id] = array(
          "codigo" => $dato["codigo"],
          "descripcion" => $dato["descripcion"],
          "capitulo" => $id_cap,
          "subcapitulo" => $id_sub
        );
      }
  
      $respuesta = array(
        "problemas" => $cie10
      );
    } else {
      $respuesta = array(
        "problemas" => NULL
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
