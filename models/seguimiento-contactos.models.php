<?php
require_once('conexion.models.php');

/**
 *  Lista de errores:
 *    0: no hay error.
 *    1: el contacto no existe.
 *    2: error en la sintaxis sql.
 *    3: los datos ingresados son inválidos.
 */
class Contactos {
  protected $link;

  public function __construct() {
    $db = new SQL;
    $this->link = $db->conectar();
  }
  public function listar() {
    /*
    contactoID,
    clienteID,
    nombre,
    mail,
    numeroCelular,
    numeroFijo,
    numeroInterno,
    */
    $q = $this->link->prepare("SELECT
      idContacto as contactoID,
      idCliente as clienteID,
      nombreCompleto as nombre,
      direccionMail as mail,
      numeroCelular,
      numeroFijo,
      numeroInterno
      FROM tblContactos"
    );

    if($q->execute()) {
      return $q->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $q->errorInfo();
    }
  }
  public function agregar($data) {
    $q = $this->link->prepare("INSERT INTO tblContactos
      (idCliente, nombreCompleto, direccionMail, numeroCelular, numeroFijo, numeroInterno)
      VALUES (
        :clienteID,
        :nombre,
        :mail,
        :numeroCelular,
        :numeroFijo,
        :numeroInterno
      )
    ");

    $q->bindParam(':clienteID', $data['clienteID'], PDO::PARAM_INT);
    $q->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
    $q->bindParam(':mail', $data['mail'], PDO::PARAM_STR);
    $q->bindParam(':numeroCelular', $data['numeroCelular'], PDO::PARAM_STR);
    $q->bindParam(':numeroFijo', $data['numeroFijo'], PDO::PARAM_STR);
    $q->bindParam(':numeroInterno', $data['numeroInterno'], PDO::PARAM_STR);


    if($q->execute()) {
      return ['status' => 0, 'id' => $this->link->lastInsertId(), 'message' => 'El contacto fue ingresado correctamente.'];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function editar($data) {
    $q = $this->link->prepare("UPDATE tblContactos SET
      idCliente = :clienteID,
      nombreCompleto  = :nombre,
      direccionMail = :mail,
      numeroCelular = :numeroCelular,
      numeroFijo = :numeroFijo,
      numeroInterno = :numeroInterno
      WHERE idContacto = :contactoID;
    ");

    $q->bindParam(':contactoID', $data['contactoID'], PDO::PARAM_INT);
    $q->bindParam(':clienteID', $data['clienteID'], PDO::PARAM_INT);
    $q->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
    $q->bindParam(':mail', $data['mail'], PDO::PARAM_STR);
    $q->bindParam(':numeroCelular', $data['numeroCelular'], PDO::PARAM_STR);
    $q->bindParam(':numeroFijo', $data['numeroFijo'], PDO::PARAM_STR);
    $q->bindParam(':numeroInterno', $data['numeroInterno'], PDO::PARAM_STR);

    if($q->execute()) {
      return ['status' => 0, 'message' => 'Contacto modificado correctamente.'];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function obtener($id) {
    $q = $this->link->prepare("SELECT
      idContacto as contactoID,
      idCliente as clienteID,
      nombreCompleto as nombre,
      direccionMail as mail,
      numeroCelular,
      numeroFijo,
      numeroInterno
      FROM tblContactos
      WHERE idContacto = :contactoID
      LIMIT 1"
    );
    $q->bindParam(':contactoID', $id, PDO::PARAM_INT);
    if($q->execute()) {
      if($q->rowCount()>0) {
        return [ 'status' => 0, 'data' => $q->fetch(PDO::FETCH_ASSOC)];
      } else {
        return [ 'status' => 1, 'data' => 'El contacto no existe.'];
      }
    } else {
      return [ 'status' => 2, 'message' => $q->errorInfo() ];
    }
  }
  public function verJson() {
    echo '<pre>';
    echo json_encode($this->listar());
    echo '</pre>';
  }
}

$datos = [
  'contactoID' => 96,
  'clienteID' => 224,
  'nombre' => 'Josecito Pérez',
  'mail' => 'josecito@mail.com',
  'numeroCelular' => '15-3728-9921',
  'numeroFijo' => '(011)2920-0092',
  'numeroInterno' => 2901,
];

$contacto = new Contactos;
// $contacto->verJson();
// echo json_encode($contacto->obtener(87));
// echo json_encode($contacto->editar($datos));
?>
