<?php
$respuesta = array();

$documento = reqtrim("documento");
$clave = reqtrim("clave");
$nacionalidad = nnreqcleanint("nacionalidad");
if ($documento && $clave && !is_null($nacionalidad)) {
  $sql = "
  select
  usr.id                          id_persona,      
  usr.dni                         lg_dni,
  usr.idnacionalidad              lg_id_nacionalidad,      
  usr.clave                       lg_clave,      
  usr.turnosbonus                 lg_turnos_bonus,
  usr.idperfil                    lg_id_perfil,
  usr.espaciente                  lg_paciente,
  usr.usuarioconfirmado           lg_usuario_confirmado,
  usr.fechaconfirmado             lg_fecha_confirmado,
  usr.fechaaltaterminoscondiciones  lg_fecha_terminoscondiciones,
  usr.usuariomodificacion         lg_usuario_modificacion,
  usr.fechamodificacion           lg_fecha_modificacion,
  usr.fechaalta                   lg_fecha_alta,
  usr.fechabaja                   lg_fecha_baja,
  usr.apellido                    usr_apellido,
  usr.apellidootro                usr_apellido_otro,
  usr.nombre                      usr_nombre,
  usr.nombreotro                  usr_nombre_otro,
  usr.fecNacimiento               usr_fecnacimiento,      
  usr.sexo                        usr_sexo,
  usr.genero                      usr_genero,
  usr.domicilio                   usr_domicilio,
  usr.codpostal                   usr_codpostal,
  usr.localidad                   usr_localidad,
  usr.partido                     usr_partido,
  usr.provincia                   usr_provincia,
  usr.paisdomicilio               usr_id_pais_domicilio,
  ps.pais                         usr_pais_domicilio,
  usr.email                       usr_email,
  usr.emailalt                    usr_email_alt,
  usr.TelefonoPrincipal           usr_telefono_principal,
  usr.TelefonoPrincipalEsCelular  usr_telefono_principal_escelular,
  usr.TelefonoSecundario          usr_telefono_secundario,
  usr.TelefonoSecundarioEsCelular usr_telefono_secundario_escelular,            
  usr.idpaciente                  pa_id,      
  usr.medseguimiento              pa_medseguimiento,
  usr.TelefonoParticular          pa_telefonoparticular,
  usr.OtrosTelefonos              pa_otrostelefonos,
  usr.CodOrigen                   pa_codorigen,
  usr.EstadoCivil                 pa_estadocivil,
  usr.Educacion                   pa_educacion,
  usr.Ocupacion                   pa_ocupacion,
  usr.Empleador                   pa_empleador,
  usr.IDOtraCobertura             pa_idotracobertura,
  usr.IDEmpresa                   pa_idempresa,
  usr.IDSector                    pa_idsector,
  usr.OcupacionEmp                pa_ocupacionemp,
  usr.EmergNombre                 pa_emergnombre,
  usr.EmergRelacion               pa_emergrelacion,
  usr.EmergTelefono               pa_emergtelefono,  
  usr.FecBaja                     pa_fecbaja,
  usr.IDQuienEnvio                pa_idquienenvio,
  usr.AltoRiesgo                  pa_altoriesgo,
  usr.Web                         pa_web,
  usr.VIP                         pa_vip,
  usr.Comentarios                 pa_comentarios,      
  usr.NoEnviarMails               pa_noenviarmails,
  usr.FecProceso                  pa_fecproceso,
  usr.UsrProceso                  pa_usrproceso,
  co.ID                           pa_cobertura_id,
  co.IDCobertura                  pa_cobertura,
  co.IDPlan                       pa_plan,
  co.NroAfiliado                  pa_nro_afiliado,
  co.IDIVACobertura               pa_iva_cobertura,
  co.Fecha                        pa_fecha_cobertura,
  usr.idusuario                   us_id,      
  usr.Codigo                      us_codigo,
  pe.descripcion                  us_codigo_descripcion,
  usr.Agenda                      us_agenda,
  usr.Titulo                      us_titulo,
  usr.LeyendaFirma                us_leyendafirma,
  usr.ImagenFirma                 us_imagenfirma,      
  usr.CUIT                        us_cuit,
  usr.IVA                         us_iva,
  usr.IIBB                        us_iibb,
  usr.SSS                         us_sss,
  usr.InscDistrito4               us_inscdistrito4,
  usr.MatNacional                 us_matnacional,
  usr.MatProvincial               us_matprovincial,
  usr.NroIIBB                     us_nroiibb,
  usr.CajaMedicos                 us_cajamedicos,
  usr.Curriculum                  us_curriculum,
  usr.Cajero                      us_cajero,
  usr.socio                       us_socio,
  usr.Baja                        us_baja
from
  personas usr
  left outer join paises ps on ps.id = usr.paisdomicilio
  left outer join perfiles pe on pe.codigo = usr.idusuario
  left outer join pac_coberturas co on co.IDPaciente = usr.IDPaciente
where
  usr.dni = ?
  and usr.idnacionalidad = ?  
  ";
  $params = array(
    "si",
    &$documento,&$nacionalidad
  );
  $mydatos = my_query($sql, $params);
  if ($mydatos !== false) {
    if ($mydatos["datos"]) {
      if (password_verify($clave, $mydatos["datos"][0]["lg_clave"])) {
        unset($mydatos["datos"][0]["lg_clave"]);
        $lg_id = $mydatos["datos"][0]["id_persona"];
        $token = randomstring($TOKENLEN);
        $sql = "
          update
            personas
          set
            token = ?
          where
            id = ?
        ";
        $params = array(
          "si",
          &$token,
          &$lg_id
        );
        $mydatos2 = my_query($sql, $params, false);
        if (($mydatos2 !== false) && ($mydatos2["filas_afectadas"] == 1)) {
          $mydatos_aux = $mydatos["datos"][0];
          unset($mydatos_aux["pa_cobertura_id"]);
          unset($mydatos_aux["pa_cobertura"]);
          unset($mydatos_aux["pa_plan"]);
          unset($mydatos_aux["pa_nro_afiliado"]);
          unset($mydatos_aux["pa_iva_cobertura"]);
          unset($mydatos_aux["pa_fecha_cobertura"]); 
          $dat = array();
          foreach ($mydatos["datos"] as $cobertura_i) {
            $dat[$cobertura_i["pa_cobertura_id"]] = array(              
              "pa_cobertura"  => $cobertura_i["pa_cobertura"],
              "pa_plan"  => $cobertura_i["pa_plan"],
              "pa_nro_afiliado"  => $cobertura_i["pa_nro_afiliado"],
              "pa_iva_cobertura"  => $cobertura_i["pa_iva_cobertura"],
              "pa_fecha_cobertura"  => $cobertura_i["pa_fecha_cobertura"]                
            );
          }
          $mydatos_aux["pa_cobertura"] = $dat;
          $respuesta = array(
            "datos" => $mydatos_aux
          );
          $codigo = 0;
          $descripcion = "Login exitoso";
        } else {
          $codigo = -25;
          $descripcion = "Error en los datos";
        }
      } else {
        $codigo = -24;
        $descripcion = "Documento o clave invalido";
      }
    } else {
      $codigo = -23;
      $descripcion = "Datos invalidos";
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
