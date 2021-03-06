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

    $q = SQL::conectar()->prepare('SELECT
      idUsuarios as usuarioID,
      userName as usuario,
      nombreCompleto as nombre,
      direccionMail as mail,
      departamento,
      nivelAcceso as acceso,
      iniciales
      FROM tblUsuarios'
    );

    if($q->execute()) {
      if($q->rowCount()>0) {
        return ['status' => 0, 'data' => $q->fetchAll(PDO::FETCH_ASSOC)];
      }
      return ['status' => 1, 'message' => 'No existe ningún usuario.'];
    }
    return['status' => 2, 'message' => $q->errorInfo()];
  }
}

$usuarios = new Usuarios;
$usuarios->listar();
?>
