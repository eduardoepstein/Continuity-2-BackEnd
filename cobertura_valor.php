<?php
$respuesta = array();

$tipo_estudio = nnreqcleanint("tipo_estudio");
$plan = nnreqcleanint("plan");

if($tipo_estudio || $plan){
  $where = "where cc.fecdesde <= curdate() and vc.fecha <= curdate() and (cc.fechasta is null or cc.fechasta >= curdate())";
  $where = $tipo_estudio ? $where. " and cc.idtipodeestudio = ?" :   
          ($plan ?$where. " and cc.idplan = ?" : $where);

  $sql = "
    select 
      cc.id,
      cc.fecdesde,
      cc.fechasta,
      cc.idcobertura,
      cc.idplan,
      cc.idtipodeestudio,
      cc.idtipocopago,
      cc.reqautorizacion,
      cc.comentarios,
      vc.importe,
      cc.idtipocopago,
      vc.copago
    from 
      contratocobertura cc 
    inner join 
      (select max(vc1.fecha) as fecha,vc1.idcontrato from valorescontrato vc1 group by vc1.idcontrato) vc2
      on vc2.idcontrato= cc.id
    inner join valorescontrato vc 
      on vc2.idcontrato = vc.idcontrato and vc.fecha = vc2.fecha
    inner join planes p 
      on cc.idplan = p.id
    inner join cal_tiposdeestudios te 
      on cc.idtipodeestudio = te.codigo
    {$where}
  ";
  $params = $tipo_estudio ? array("i",&$tipo_estudio) :   
    ($plan ? array("i",&$plan) : NULL);    

  $mydatos = my_query($sql, $params);
    
  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";
    $datos = array();    
    
    if ($mydatos["datos"]) {
      
      $agrupador_estudio = -1;  
      $agrupador_plan = -1;  
      $coberturas = array();

      foreach ($mydatos["datos"] as $contratos) {
        $id_estudio = $contratos["idtipodeestudio"];  
        $id_plan = $contratos["idplan"];  

        $contrato = array(                               
          "id_contrato"  => $contratos["id"],
          "id_cobertura"  => $contratos["idcobertura"],
          "fecha_desde"  => $contratos["fecdesde"],
          "fecha_hasta"  => $contratos["fechasta"] ?? null,                    
          "requiere_autorizacion"  => $contratos["reqautorizacion"],
          "comentarios"  => $contratos["comentarios"],
          "importe"  => $contratos["importe"],
          "idtipocopago"  => $contratos["idtipocopago"],
          "copago"  => $contratos["copago"]   
        );

        if($agrupador_estudio != $id_estudio) {
          $agrupador_estudio = $id_estudio;
          $coberturas[$agrupador_estudio] = array(
            "planes" => array(
              $contratos["idplan"] => $contrato
              )
          );

        } else {
          if($agrupador_plan != $id_plan){
            $agrupador_plan = $id_plan;
            $coberturas[$agrupador_estudio]["planes"][$id_plan] = $contrato;
          }
          else{
           //
          }
        }
      
      }    
      $respuesta = array(
        "tipos_estudio" => $coberturas
      );
    } else {
      $respuesta = array(
        "tipos_estudio" => NULL
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