<?php
require_once('conexion.models.php');

/**
 *
 */
class Clientes {
  protected $link;

  public function __construct($link = null) {
    if(!$link) {
      $db = new SQL;
      $this->link = $db->conectar();
    } else {
      $this->link = $link;
    }
  }
  public function listar() {
    /*
      clienteID,
      nombre,
      domicilio,
      aliases
    */
    $q = $this->link->prepare('SELECT
      idCliente as clienteID,
      razonSocial as nombre,
      domicilioPrincipal as domicilio,
      aliases
      FROM tblClientes');
    if($q->execute()) {

      return $q->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $q->errorInfo();
    }
  }
  public function agregar($data) {
    $q = $this->link->prepare("INSERT INTO tblClientes
      (razonSocial, domicilioPrincipal, aliases)
      VALUES (
        :nombre,
        :domicilio,
        :alias
      )
    ");

    $q->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
    $q->bindParam(':domicilio', $data['domicilio'], PDO::PARAM_STR);
    $q->bindParam(':alias', $data['alias'], PDO::PARAM_STR);

    if($q->execute()) {
      return ['status' => 0, 'id' => $this->link->lastInsertId(), 'message' => 'El cliente fue ingresado correctamente.'];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function editar($data) {
    $q = $this->link->prepare("UPDATE tblClientes SET
      razonSocial = :nombre,
      domicilioPrincipal  = :domicilio,
      aliases = :alias
      WHERE idCliente = :clienteID
    ");

    $q->bindParam(':clienteID', $data['clienteID'], PDO::PARAM_INT);
    $q->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
    $q->bindParam(':domicilio', $data['domicilio'], PDO::PARAM_STR);
    $q->bindParam(':alias', $data['alias'], PDO::PARAM_STR);

    if($q->execute()) {
      return ['status' => 0, 'message' => 'Cliente modificado correctamente.'];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function obtener($id) {
    $q = $this->link->prepare("SELECT
      idCliente as clienteID,
      razonSocial as nombre,
      domicilioPrincipal as domicilio,
      aliases
      FROM tblClientes
      WHERE idCliente = :clienteID
      LIMIT 1
    ");
    $q->bindParam(':clienteID', $id, PDO::PARAM_INT);
    if($q->execute()) {
      if($q->rowCount()>0) {
        return [ 'status' => 0, 'data' => $q->fetch(PDO::FETCH_ASSOC)];
      } else {
        return [ 'status' => 1, 'message' => 'El cliente no existe.' ];
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

// $datos = [
//   'clienteID' => 160,
//   'nombre' => 'Coorporación benito',
//   'domicilio' => 'Evergreen 123',
//   'alias' => null
// ];

// $cliente = new Clientes;
// $cliente->verJson();
// echo json_encode($cliente->obtener(57));
// echo json_encode($cliente->editar($datos));
?>
