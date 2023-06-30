<?php
$respuesta = array();
$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");
$dislipemia = nnreqcleanint("dislipemia")?? 0;
$obesidad = nnreqcleanint("obesidad")?? 0;
$tabaquismo = nnreqcleanint("tabaquismo")?? 0;
$tabacopasivo = nnreqcleanint("tabacopasivo")?? 0;
$extabaquista = nnreqcleanint("extabaquista")?? 0;
$hipertension = nnreqcleanint("hipertension")?? 0;
$sedentarismo = nnreqcleanint("sedentarismo")?? 0;
$diabetes = nnreqcleanint("diabetes")?? 0;
$antfamiliares = nnreqcleanint("antfamiliares")?? 0;
$estres = nnreqcleanint("estres")?? 0;
$dietainadecuada = nnreqcleanint("dietainadecuada")?? 0;
$climaterio = nnreqcleanint("climaterio")?? 0;
$tratcolesterol = nnreqcleanint("tratcolesterol")?? 0;
$tratdiabetes = nnreqcleanint("tratdiabetes")?? 0;
$trathipertension = nnreqcleanint("trathipertension")?? 0;
$prevencionsec = nnreqcleanint("prevencionsec")?? 0;

$usr_proceso = reqtrim("usr_proceso") ?? '';

/*"1" => array("descripción" => "Obesidad"),
"2" => array("descripción" => "Tabaquismo"),
"3" => array("descripción" => "TabacoPasivo"),
"4" => array("descripción" => "ExTabaquista"),
"5" => array("descripción" => "Hipertension"),
"6" => array("descripción" => "Sedentarismo"),
"7" => array("descripción" => "Diabetes"),
"8" => array("descripción" => "AntFamiliares"),
"9" => array("descripción" => "Estres"),
"10" => array("descripción" => "DietaInadecuada"),
"11" => array("descripción" => "Climaterio"),
"12" => array("descripción" => "TratColesterol"),
"13" => array("descripción" => "TratDiabetes"),
"14" => array("descripción" => "TratHipertension"),
"14" => array("descripción" => "PrevencionSec")
*/
if ($id || ($id_paciente && $usr_proceso)) {
$where = $id ? " where fac.id = ? ": 
  ($id_paciente ? " where fac.idpaciente = ? " :
    null);
  $sql = "
  select  

    fac.id,
    fac.idpaciente,
    fac.usrproceso,
    fac.dislipemia,
    fac.obesidad,
    fac.tabaquismo,
    fac.tabacopasivo,
    fac.extabaquista,
    fac.hipertension,
    fac.sedentarismo,
    fac.diabetes,
    fac.antfamiliares,
    fac.estres,
    fac.dietainadecuada,
    fac.climaterio,
    fac.tratcolesterol,
    fac.tratdiabetes,
    fac.trathipertension,
    fac.prevencionsec



  from pac_factoresderiesgo  fac   
  {$where}
  order by id desc
  ";    
  $params = $id ? array("i",&$id) : 
    ($id_paciente ? array("i",&$id_paciente) : 
      null);      
  $mydatos = my_query($sql, $params);  

  if ($mydatos !== false) {
    $codigo = 0;
    $descripcion = "OK";    
    $mydatos2 = NULL;  
    if ($mydatos["datos"]) {   
      $id = $id ?? $mydatos["datos"][0]["id"];             
      $sql = "
      update
          pac_factoresderiesgo
      set
        dislipemia = ?,
        obesidad = ?,
        tabaquismo = ?,
        tabacopasivo = ?,
        extabaquista= ?,
        hipertension = ?,
        sedentarismo = ?,
        diabetes = ?,
        antfamiliares = ?,
        estres = ?,
        dietainadecuada = ?,
        climaterio = ?,
        tratcolesterol = ?,
        tratdiabetes = ?,
        trathipertension = ?,
        prevencionsec = ?,
        fecproceso = SYSDATE(),      
        usrproceso = ?        
      where
        id = ?
      ";
      $params = array("iiiiiiiiiiiiiiiisi"
      ,&$dislipemia,&$obesidad,&$tabaquismo,&$tabacopasivo,&$extabaquista,&$hipertension,&$sedentarismo,&$diabetes,&$antfamiliares,&$estres,&$dietainadecuada,&$climaterio,&$tratcolesterol,&$tratdiabetes,&$trathipertension,&$prevencionsec,&$usr_proceso,&$id);  
    } else {
      $sql = "
      insert into
      pac_factoresderiesgo(
          idpaciente,          
          dislipemia,
          obesidad,
          tabaquismo,
          tabacopasivo,
          extabaquist,
          hipertension,
          sedentarismo,
          diabetes,
          antfamiliares,
          estres,
          dietainadecuada,
          climaterio,
          tratcolesterol,
          tratdiabetes,
          trathipertension,
          prevencionsec,
          usrproceso
        )
      values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
      ";      
      $params = array("iiiiiiiiiiiiiiiis"
      ,&$id_paciente,&$dislipemia,&$obesidad,&$tabaquismo,&$tabacopasivo,&$extabaquista,&$hipertension,&$sedentarismo,&$diabetes,&$antfamiliares,&$estres,&$dietainadecuada,&$climaterio,&$tratcolesterol,&$tratdiabetes,&$trathipertension,&$prevencionsec,&$usr_proceso);  
    }     
    
    $mydatos2 = my_query($sql,$params,false);    
    if($mydatos2 == true && ($mydatos2["insert_id"] || ($mydatos2["filas_afectadas"] == 1))) {      
      $id = $id ?? $mydatos2["insert_id"] ?? -1;               
      $respuesta = array(
        "id" => $id
      );    
    } else{
      $codigo = -23;
      $descripcion = "Error al cargar los datos";
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