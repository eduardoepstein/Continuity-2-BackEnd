<?php
$respuesta = array();
$codigo_medicamento = nnreqcleanint("codigo");
$descripcion = nreqtrim("descripcion") ? '%'.nreqtrim("descripcion").'%' : null;
$codigo_droga = nnreqcleanint("codigo_droga");
$descripcion_droga = nreqtrim("descripcion_droga") ? '%'.nreqtrim("descripcion_droga").'%' : null;

if($codigo_medicamento ||$descripcion || $codigo_droga || $descripcion_droga){
  
  $where = $codigo_medicamento ? " where med.codigo = ? " : ( 
    $codigo_droga ? " where drg.codigo = ? ": ( 
      $descripcion ? " where med.descripcion like ? " : ( 
        $descripcion_droga ? " where drg.descripcion like ? " : null)));  
        $where = $where." and drg.baja = 0 and med.baja = 0 ";
  $sql = "
    select
      med.codigo,
      med.descripcion,        
      drg.codigo as codigo_droga,
      drg.descripcion as descripcion_droga,      
      med.kpro,
      med.kpre
    from drogas drg
    left join drogasmedicamentos drm
      on drg.codigo = drm.iddroga
    left join medicamentos med
      on drm.idmedicamento = med.codigo
    {$where}      
    order by drg.descripcion asc
  ";

  $params = $codigo_medicamento ? array("i",&$codigo_medicamento) : ( 
    $codigo_droga ? array("i",&$codigo_droga) : ( 
      $descripcion ? array("s",&$descripcion) : ( 
        $descripcion_droga ? array("s",&$descripcion_droga) : null)));  

  $mydatos = my_query($sql, $params);

  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";
    $datos = array();
    if ($mydatos["datos"]) {   
      $medicamentos = array();    
      $id_droga = -1;         
      $codigo_medicamento = -1;      
      foreach ($mydatos["datos"] as $dato) {        
        $id_drg = $dato["codigo_droga"];        
        $id_med = $dato["codigo"];        
        $medicamento = array(
          "descripcion" => $dato["descripcion"]
        );
        if($id_drg != $id_droga){
          $id_droga = $id_drg;
          $droga = array(            
            "descripcion_droga" => $dato["descripcion_droga"],
            "medicamento" => $medicamento ? array($dato["codigo"] => $medicamento) : null
          );
          $medicamentos[$id_drg] = $droga;
        } elseif ($id_med) {          
          $medicamentos[$id_drg]["medicamento"][$id_med] = $medicamento;          
        }
      }  
      $respuesta = array(
        "medicamentos" => $medicamentos
      );
    } else {
      $respuesta = array(
        "medicamentos" => NULL
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
