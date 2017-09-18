<?php
require_once('conexion.models.php');
require_once('seguimiento-bas.models.php');
require_once('seguimiento-clientes.models.php');
require_once('seguimiento-contactos.models.php');


class Pedidos {
  protected $link;

  protected $titulo;
  protected $descripcion;

  protected $categoria;

  protected $cliente;
  protected $contacto;

  protected $usuario;

  public function __construct() {
    $db = new SQL;
    $this->link = $db->conectar();
  }
  public function listar() {
    /*
      pedidoID,
      clienteID,
      contactoID,
      contactoMail,
      categoriaID,
      responsableID,
      usuarioID,
      bas,
      titulo,
      descripcion,
      fecha,

      estadoActual = ?,
      pedidosCotizado = ?,
      estado = ?,
      pedidosAlerta = ?,
      pedidosAsignado = ?,
    */
    $q = $this->link->prepare('SELECT
      pedidosID as pedidoID,
      pedidosCliente as clienteID,
      pedidosContacto as contactoID,
      pedidosMailContacto as contactoMail,
      pedidosCategoria as categoriaID,
      responsableDesignado as responsableID,
      pedidosUsuario as usuarioID,
      pedidosNro as bas,
      pedidosTitulo as titulo,
      pedidosDescripcion as descripcion,
      pedidosFecha as fecha,
      estadoActual as proceso
      FROM tblPedidos');
    if($q->execute()) {

      return $q->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $q->errorInfo();
    }
  }
  public function ingresar($data) {
    $q = $this->link->prepare("INSERT INTO tblpedidos
      (
        pedidosCliente,
        pedidosContacto,
        pedidosCategoria,
        pedidosUsuario,
        pedidosNro,
        pedidosTitulo,
        pedidosDescripcion,
        pedidosFecha,
        estadoActual
      ) VALUES (
        :clienteID,
        :contactoID,
        :categoriaID,
        :usuarioID,
        :bas,
        :titulo,
        :descripcion,
        CURRENT_TIMESTAMP,
        1
      )");
    $q->bindParam(':clienteID',$data['clienteID'],PDO::PARAM_INT);
    $q->bindParam(':contactoID',$data['contactoID'],PDO::PARAM_INT);
    $q->bindParam(':categoriaID',$data['categoriaID'],PDO::PARAM_INT);
    $q->bindParam(':usuarioID',$data['usuarioID'],PDO::PARAM_INT);
    $q->bindParam(':bas',$data['bas'],PDO::PARAM_STR);
    $q->bindParam(':titulo',$data['titulo'],PDO::PARAM_STR);
    $q->bindParam(':descripcion',$data['descripcion'],PDO::PARAM_STR);

    if($q->execute()) {
      return ['status' => 0, 'id' => $this->link->lastInsertId(), 'message' => 'El pedido fue ingresado correctamente.'];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function editar($data) {
    // pedidosUsuario = :usuarioID,
    // pedidosNro = :bas,
    $q = $this->link->prepare("UPDATE tblPedidos SET
      pedidosCliente = :clienteID,
      pedidosContacto = :contactoID,
      pedidosMailContacto = :contactoMail,
      pedidosCategoria = :categoriaID,
      pedidosTitulo = :titulo,
      pedidosDescripcion = :descripcion
      WHERE
      pedidosID = :pedidoID
    ");

    $q->bindParam(':clienteID',$data['clienteID'],PDO::PARAM_INT);
    $q->bindParam(':contactoID',$data['contactoID'],PDO::PARAM_INT);
    $q->bindParam(':contactoMail',$data['contactoMail'],PDO::PARAM_STR);
    $q->bindParam(':categoriaID',$data['categoriaID'],PDO::PARAM_INT);
    $q->bindParam(':titulo',$data['titulo'],PDO::PARAM_STR);
    $q->bindParam(':descripcion',$data['descripcion'],PDO::PARAM_STR);
    $q->bindParam(':pedidoID', $data['pedidoID'],PDO::PARAM_INT);

    if($q->execute()) {
      return $this->link->lastInsertId();
    } else {
      return $q->errorInfo();
    }
  }
  public function verJson() {
    $todos = $this->listar();
    $filtrado = [];
    foreach ($todos as $key_p => $pedidos) {
      foreach ($pedidos as $key_s => $value) {
        if($key_s !== descripcion) {

          $filtrado[$key_p][$key_s] = $value;
        }
      }
    }
    echo '<pre>';
    echo json_encode($filtrado);
    echo '</pre>';
  }
}

$datos = [
  'pedidoID' => 281,
  'clienteID' => 4,
  'contactoID' => 30,
  'contactoMail' => 'mail@bas-is.com.ar',
  'categoriaID' => 3,
  'usuarioID' => 1,
  'bas' => 'BAS18CA-0002',
  'titulo' => 'Título de prueba',
  'descripcion' => 'Descripción de prueba'
];

$pedido = new Pedidos;
// $pedido->verJson();
//echo json_encode($pedido->listar());
//echo json_encode($pedido->ingresar($datos));
//echo json_encode($pedido->editar($datos));
?>
