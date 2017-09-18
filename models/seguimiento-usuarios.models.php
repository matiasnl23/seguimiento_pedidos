<?php
require_once('conexion.models.php');

/**
 *
 */
class Usuarios
{

  // function __construct(argument)
  // {
  //   # code...
  // }

  function listar() {
    /*
      usuarioID,
      usuario,
      nombre,
      mail,
      departamento,
      acceso,
      iniciales,
    */

    $q = SQL::conectar()->prepare('SELECT idUsuarios as usuarioID, userName as usuario, nombreCompleto as nombre, direccionMail as mail, departamento, nivelAcceso as acceso, iniciales FROM tblUsuarios');
    if($q->execute()) {

      return $q->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $q->errorInfo();
    }
  }
}


?>
