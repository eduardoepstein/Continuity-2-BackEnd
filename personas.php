<?php
$respuesta = array();
$id = nnreqcleanint("id_persona");
$documento = reqtrim("documento");
$nacionalidad = nnreqcleanint("nacionalidad");
$apellido = nnreqcleanint("apellido");
if ($documento && !is_null($nacionalidad) || $id || $apellido) {

  $where = $id ? " where usr.id = ?" : ( 
    $documento ? " where usr.dni = ? and usr.idnacionalidad = ?": " where concat(apellido,apellidootro) like ?");

  $sql = "
  select
  usr.id                          id_persona,      
  usr.dni                         lg_dni,
  usr.idnacionalidad              lg_id_nacionalidad,          
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
  usr.IDQuienEnvio                pa_idquienenvio,
  usr.AltoRiesgo                  pa_altoriesgo,
  usr.Web                         pa_web,
  usr.VIP                         pa_vip,
  usr.Comentarios                 pa_comentarios,      
  usr.NoEnviarMails               pa_noenviarmails,
  usr.FecProceso                  pa_fecproceso,
  usr.UsrProceso                  pa_usrproceso,
  usr.idusuario                   us_id,        
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
{$where}
  ";
  $params = $id ? array("i",&$id): 
  $documento ? array("si",&$documento,&$nacionalidad) :
  array("s",&$apellido);

  $mydatos = my_query($sql, $params);
  if ($mydatos !== false) {
    if ($mydatos["datos"]) {      
      $codigo = 0;
      $descripcion = "OK";
      $respuesta = array(
        "datos" => $mydatos["datos"][0]
      );
      
    } else {
          $codigo = -23;
          $descripcion = "Error en los datos";
        }      
  } else {
      $codigo = -22;
      $descripcion = "Datos invalidos";
  }  
} else {
  $codigo = -21;
  $descripcion = "Faltan argumentos requeridos";
}

enviar_respuesta_datos($codigo, $descripcion, $respuesta);

//ñ
