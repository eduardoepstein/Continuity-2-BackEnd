<?php
$respuesta = array();
$id_paciente = nnreqcleanint("id_paciente");
if (!is_null($id_paciente)) {    
  $sql = "
    select  
      evl.id,
      evl.idpaciente,
      evl.fecha,
      evl.evolucion,
      cst.id id_consulta,
      cst.fecha fecha_consulta,
      cst.fc,
      cst.pas,
      cst.pad,
      cst.peso,
      cst.altura,
      cst.percintura,
      cst.exfisico,
      cst.cerrado,
      cie.id idcie10,
      cie.codigo,
      cie.descripcion,
      usr.nombre
  from pac_evoluciones  evl
    left join usuarios usr on  usr.codigo = evl.usrProceso
    left outer join pac_consultas cst on cst.idpaciente = ? and cst.Fecha = evl.fecha
    left outer join pac_problemasevolucion prb on prb.idevolucion = evl.id
    inner join cie10 cie on cie.id = prb.idproblema
  where evl.idpaciente = ?
  order by evl.fecha desc
";
$params = array("ii",&$id_paciente,&$id_paciente);
$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  if($mydatos["datos"]){       
    $evoluciones = array();
    $id_evolucion = -1;
    $id_consulta = -1;
    $id_problema = -1;    
    
    foreach ($mydatos["datos"] as $dato) {
      $id_evl = $dato["id"];
      $id_csl = $dato["id_consulta"];
      $id_prb = $dato["idcie10"];

      $problema = array(
        "id" => $dato["idcie10"],
        "codigo" => $dato["codigo"],
        "descrpcion" => $dato["descripcion"]); 

      $consulta = array(
        "fc"  => $dato["fc"],
        "pas"  => $dato["pas"],
        "pad"  => $dato["pad"],
        "peso"  => $dato["peso"] != null ? floatval($dato["peso"]) : null,
        "altura"  => $dato["altura"] != null ? floatval($dato["altura"]) : null,
        "per_cintura"  => $dato["percintura"],
        "examen_fisico"  => $dato["exfisico"],
        "fecha_consulta"  => $dato["fecha_consulta"],
        "cerrado"  => $dato["cerrado"]        
      );

      if($id_evl != $id_evolucion){
        $id_evolucion = $id_evl;
        $evoluciones[$id_evolucion] = array(          
          "medico"  => $dato["nombre"],          
          "evolucion"  => $dato["evolucion"],          
          "fecha"  => $dato["fecha"],
          "consultas" => $dato["id_consulta"] ? array($dato["id_consulta"] => $consulta) : null,
          "problemas" => $id_prb ? array($problema) : null
        );

      } else {
        if($id_prb != $id_problema){
          $id_problema = $id_prb;
          $evoluciones[$id_evolucion]["problemas"][] = $problema ;
        }
        if($id_csl != $id_consulta){
          $id_consulta = $id_csl;
          if($dato["id_consulta"]){
            $evoluciones[$id_evolucion]["consultas"][$id_consulta] = $consulta;
          }          
        }
      }      
    }
    $respuesta = array(
      "evoluciones" => $evoluciones
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
