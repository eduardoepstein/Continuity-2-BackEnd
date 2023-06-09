<?php
$respuesta = array();
$id_paciente = nnreqcleanint("id_paciente");
if (!is_null($id_paciente)) {    

  //      dtl.horainicial,
  //dtl.frecuencia,
  $sql = "
    select  
      mdc.id,
      mdc.idpaciente,
      mdc.fecdesde,
      mdc.fechasta,
      med.codigo,
      med.descripcion,
      datediff(mdc.fechasta,mdc.fecdesde) duracion_tratamiento,
      drg.codigo as codigo_droga,
      drg.descripcion as descripcion_droga,      
      med.kpro,
      med.kpre,
      dtl.id id_detallemedicamento,            
      dtl.fecha,      
      dtl.frecuencia,
      dtl.cantidad,
      dtl.horainicial,      
      dtl.tld_dosisdiaria,
      dtl.tld_dosismaniana,
      dtl.tld_dosismediodia,
      dtl.tld_dosistarde,
      dtl.tld_dosisnoche,
      dtl.tld_codunidad,
      dtl.lu_dosisdiaria,
      dtl.lu_dosismaniana,
      dtl.lu_dosismediodia,
      dtl.lu_dosistarde,
      dtl.lu_dosisnoche,
      dtl.lu_codunidad,
      dtl.ma_dosisdiaria,
      dtl.ma_dosismaniana,
      dtl.ma_dosismediodia,
      dtl.ma_dosistarde,
      dtl.ma_dosisnoche,
      dtl.ma_codunidad,
      dtl.mi_dosisdiaria,
      dtl.mi_dosismaniana,
      dtl.mi_dosismediodia,
      dtl.mi_dosistarde,
      dtl.mi_dosisnoche,
      dtl.mi_codunidad,
      dtl.ju_dosisdiaria,
      dtl.ju_dosismaniana,
      dtl.ju_dosismediodia,
      dtl.ju_dosistarde,
      dtl.ju_dosisnoche,
      dtl.ju_codunidad,
      dtl.vi_dosisdiaria,
      dtl.vi_dosismaniana,
      dtl.vi_dosismediodia,
      dtl.vi_dosistarde,
      dtl.vi_dosisnoche,
      dtl.vi_codunidad,
      dtl.sa_dosisdiaria,
      dtl.sa_dosismaniana,
      dtl.sa_dosismediodia,
      dtl.sa_dosistarde,
      dtl.sa_dosisnoche,
      dtl.sa_codunidad,
      dtl.do_dosisdiaria,
      dtl.do_dosismaniana,
      dtl.do_dosismediodia,
      dtl.do_dosistarde,
      dtl.do_dosisnoche,
      dtl.do_codunidad,
      dtl.todoslosdias,
      dtl.lunes,
      dtl.martes,
      dtl.miercoles,
      dtl.jueves,
      dtl.viernes,
      dtl.sabado,
      dtl.domingo,
      dtl.observaciones,
      dtl.fecproceso,
      dtl.usrproceso,
      dss.id dosis_id,
      dss.cantidad dosis_cantidad,
      dss.hora dosis_hora
    from pac_medicacion mdc
    inner join medicamentos med 
      on med.codigo = mdc.codmedicamento   
    left join drogasmedicamentos drm
      on drm.idmedicamento = med.codigo
    left join drogas drg
      on drg.codigo = drm.iddroga
    inner join pac_detallemedicacion dtl
      on dtl.idmedicacion = mdc.id
    left join pac_detallemedicamentodosis dss
      on dss.iddetallemedicamento = dtl.id
  where mdc.idpaciente = ?
  order by mdc.id desc
";
//where evl.idpaciente = ? and drg.baja = 0 and med.baja = 0
//relación uno a muchos id_medicamento detalles
$params = array("i",&$id_paciente);
$mydatos = my_query($sql, $params);

if ($mydatos !== false) {  
  $codigo = 0;
  $descripcion = "OK";    
  if($mydatos["datos"]){       
    $medicamento = null;
    $medicamentos = array();
    $id_medicamento = -1; 
    $id_detallemedicamento = -1;  
    $id_droga = -1;  
    $fecha = null;
    $droga_codigo_lista = array();
    foreach ($mydatos["datos"] as $dato) {      
      $id_mdc = $dato["id"];
      $id_detalle = $dato["id_detallemedicamento"];
      $id_dosis = $dato["dosis_id"];
      $id_drg = $dato["codigo_droga"];      
      
      $dosis = array(        
        "hora" => $dato["dosis_hora"] ? (new DateTime($dato["dosis_hora"]))->format('H:i:s') : null,    
        "cantidad" => $dato["dosis_cantidad"]);

      $detalle = array(        
        "fecha" => $dato["fecha"],
        "hora_inicial" => $dato["horainicial"],
        "frecuencia" => $dato["frecuencia"],
        "cantidad" => $dato["cantidad"],        
        "tld_dosisdiaria" => $dato["tld_dosisdiaria"],
        "tld_dosismaniana" => $dato["tld_dosismaniana"],
        "tld_dosismediodia" => $dato["tld_dosismediodia"],
        "tld_dosistarde" => $dato["tld_dosistarde"],
        "tld_dosisnoche" => $dato["tld_dosisnoche"],
        "unidad" => $dato["tld_codunidad"],
        "lu_dosisdiaria" => $dato["lu_dosisdiaria"],
        "lu_dosismaniana" => $dato["lu_dosismaniana"],
        "lu_dosismediodia" => $dato["lu_dosismediodia"],
        "lu_dosistarde" => $dato["lu_dosistarde"],
        "lu_dosisnoche" => $dato["lu_dosisnoche"],
        "lu_codunidad" => $dato["lu_codunidad"],
        "ma_dosisdiaria" => $dato["ma_dosisdiaria"],
        "ma_dosismaniana" => $dato["ma_dosismaniana"],
        "ma_dosismediodia" => $dato["ma_dosismediodia"],
        "ma_dosistarde" => $dato["ma_dosistarde"],
        "ma_dosisnoche" => $dato["ma_dosisnoche"],
        "ma_codunidad" => $dato["ma_codunidad"],
        "mi_dosisdiaria" => $dato["mi_dosisdiaria"],
        "mi_dosismaniana" => $dato["mi_dosismaniana"],
        "mi_dosismediodia" => $dato["mi_dosismediodia"],
        "mi_dosistarde" => $dato["mi_dosistarde"],
        "mi_dosisnoche" => $dato["mi_dosisnoche"],
        "mi_codunidad" => $dato["mi_codunidad"],
        "ju_dosisdiaria" => $dato["ju_dosisdiaria"],
        "ju_dosismaniana" => $dato["ju_dosismaniana"],
        "ju_dosismediodia" => $dato["ju_dosismediodia"],
        "ju_dosistarde" => $dato["ju_dosistarde"],
        "ju_dosisnoche" => $dato["ju_dosisnoche"],
        "ju_codunidad" => $dato["ju_codunidad"],
        "vi_dosisdiaria" => $dato["vi_dosisdiaria"],
        "vi_dosismaniana" => $dato["vi_dosismaniana"],
        "vi_dosismediodia" => $dato["vi_dosismediodia"],
        "vi_dosistarde" => $dato["vi_dosistarde"],
        "vi_dosisnoche" => $dato["vi_dosisnoche"],
        "vi_codunidad" => $dato["vi_codunidad"],
        "sa_dosisdiaria" => $dato["sa_dosisdiaria"],
        "sa_dosismaniana" => $dato["sa_dosismaniana"],
        "sa_dosismediodia" => $dato["sa_dosismediodia"],
        "sa_dosistarde" => $dato["sa_dosistarde"],
        "sa_dosisnoche" => $dato["sa_dosisnoche"],
        "sa_codunidad" => $dato["sa_codunidad"],
        "do_dosisdiaria" => $dato["do_dosisdiaria"],
        "do_dosismaniana" => $dato["do_dosismaniana"],
        "do_dosismediodia" => $dato["do_dosismediodia"],
        "do_dosistarde" => $dato["do_dosistarde"],
        "do_dosisnoche" => $dato["do_dosisnoche"],
        "do_codunidad" => $dato["do_codunidad"],
        "todoslosdias" => $dato["todoslosdias"],
        "lunes" => $dato["lunes"],
        "martes" => $dato["martes"],
        "miercoles" => $dato["miercoles"],
        "jueves" => $dato["jueves"],
        "viernes" => $dato["viernes"],
        "sabado" => $dato["sabado"],
        "domingo" => $dato["domingo"],
        "observaciones" => $dato["observaciones"],
        "fecproceso" => $dato["fecproceso"],
        "usrproceso" => $dato["usrproceso"],
        "dosis" => $dato["dosis_id"] ? array(
          $dato["dosis_id"] => $dosis) : null
      );
      
      $droga = array(
        "codigo" => $dato["codigo_droga"],
        "descripcion" => $dato["descripcion_droga"]
      );

      if($id_mdc != $id_medicamento){
        if($medicamento){
          if($fecha && $fecha <= date("Y-m-d")){
            $medicamentos["inactivos"][$id_medicamento] = $medicamento;
          }
          else{
            $medicamentos["activos"][$id_medicamento] = $medicamento;
          }  
        }
        $id_medicamento = $id_mdc;
        $fecha = $dato["fechasta"] ? (new DateTime($dato["fechasta"]))->format('Y-m-d') : null;    
        $droga_codigo_lista = array($id_drg);        
        $medicamento = array(          
          "id_paciente"  => $dato["idpaciente"],          
          "fecha_desde"  => $dato["fecdesde"],
          "fecha_hasta"  => $dato["fechasta"],          
          "duracion_tratamiento" => $dato["duracion_tratamiento"],          
          "id_tratamiento"  => (($dato["duracion_tratamiento"] !== null) && intval($dato["duracion_tratamiento"]) < 30) ? 1 : 0,
          "codigo" => $dato["codigo"],
          "descripcion" => $dato["descripcion"],
          "kpro" => $dato["kpro"],
          "kpre" => $dato["kpre"],
          "drogas" => $dato["codigo_droga"] ? array(
          $droga) : null,
          "detalles" => $dato["id_detallemedicamento"] ? array(
            $dato["id_detallemedicamento"] => $detalle) : null
        );         
      } else {
        if($id_detalle != $id_detallemedicamento) {        
          $id_detallemedicamento = $id_detalle;          
          $medicamento["detalles"][$id_detalle] = $detalle;
        } else{
          if($dato["dosis_id"]){
            $medicamento["detalles"][$id_detalle]["dosis"][$id_dosis] = $dosis;
          }
        }     
        if($id_drg != $id_droga) {
          if(!(in_array($id_drg,$droga_codigo_lista,false))){
            $droga_codigo_lista[] = $id_drg;  
            $id_droga = $id_drg;          
            $medicamento["drogas"][] = $droga;         
          }
        } 
      } 
    }
    $respuesta = array(
      "medicamentos" => $medicamentos
    );
  } else {
    $respuesta = array(
      "medicamentos" => null
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
