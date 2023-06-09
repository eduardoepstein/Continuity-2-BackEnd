
<?php

$id = nnreqcleanint("id");
$id_paciente = nnreqcleanint("id_paciente");

if($parametrosobligatorios){

  $sql = "select from table where 1";
  $params = array();
  $mydatos =  my_query($sql, $params);
  if($mydatos && $id){
    //update
  } elseif($mydatos) {
    //error registro existente
  } elseif($id){
    //error
  } else {
    //inserto
  }
} else{
    //error
}

//ñ
