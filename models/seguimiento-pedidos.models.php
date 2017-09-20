<?php
require_once('conexion.models.php');
require_once('seguimiento-bas.models.php');
require_once('seguimiento-clientes.models.php');
require_once('seguimiento-contactos.models.php');


class Pedidos {
  protected $db;
  protected $link;

  protected $id;
  protected $bas;
  protected $titulo;
  protected $descripcion;

  protected $categoria;
  protected $proceso;

  protected $clienteID;
  protected $contactoID;

  protected $usuario;

  public function __construct($id = null) {
    $db = new SQL;
    $this->link = $db->conectar();

    $this->proceso = 0;

    if($id!==null) {
      $pedido = $this->getPedido($id);
      if($pedido['status']!==0) {
        $this->id = $pedido['pedidoID'];

        $this->bas = $pedido['bas'];
        $this->titulo = $pedido['titulo'];
        $this->descripcion = $pedido['descripcion'];

        $this->categoria = $pedido['categoria'];
        $this->proceso = $pedido['proceso'];

        $this->clienteID = $pedido['clienteID'];
        $this->contactoID = $pedido['contactoID'];
      }
    }
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
  public function getPedido($id) {
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
      FROM tblPedidos
      WHERE pedidosID = :pedidosID
    ');

    $q->bindParam(':pedidosID', $id, PDO::PARAM_INT);
    if($q->execute()) {
      if($q->rowCount()===1) {
        return ['status' => 0, 'data' => $q->fetch(PDO::FETCH_ASSOC)];
      }
      return ['status' => 1, 'message' => 'El pedido solicitado no existe.'];
    }
    return ['status' => 2, 'message' => $q->errorInfo()];
  }
  public function ingresar($data) {
    // Se inician la transacción con la base de datos.
    $this->link->beginTransaction();

    /* Acciones relacionadas con la asignación y actualización del BAS */
    $_bas = new BAS($this->link);
    $basTrabajo = $_bas->disponible('CA');
    if($basTrabajo['status']!=0) {
      $this->link->rollBack();
      return $basTrabajo;
    } else {
      $this->bas = $basTrabajo['data']['inicial'].$basTrabajo['data']['anual'].$basTrabajo['data']['area'].'-'.sprintf('%04d', $basTrabajo['data']['incremental']);

      $basActualizar = $_bas->incrementar();
      if($basActualizar['status']!=0) {
        $this->link->rollBack();
        return $basActualizar;
      }
    }

    /* Acciones relacionadas con la verificación de la existencia del cliente, y del contacto
    en el cliente */
    $verificarDatos = $this->verificarDatos($data);
    if($verificarDatos['status']!==0) {
      $this->link->rollBack();
      return $verificarDatos;
    }

    /* Se realiza la carga del pedido */
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
      )
    ");
    $q->bindParam(':bas',$this->bas,PDO::PARAM_STR);
    $q->bindParam(':clienteID',$this->clienteID,PDO::PARAM_INT);
    $q->bindParam(':contactoID',$this->contactoID,PDO::PARAM_INT);
    $q->bindParam(':categoriaID',$data['categoriaID'],PDO::PARAM_INT);
    $q->bindParam(':usuarioID',$data['usuarioID'],PDO::PARAM_INT);
    $q->bindParam(':titulo',$data['titulo'],PDO::PARAM_STR);
    $q->bindParam(':descripcion',$data['descripcion'],PDO::PARAM_STR);

    if($q->execute()) {
      $id = $this->link->lastInsertId();
      $this->link->commit();
      return ['status' => 0, 'id' => $id, 'message' => 'El pedido fue ingresado correctamente.'];
    } else {
      $this->link->rollBack();
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function editar($data) {
    // Se inician la transacción con la base de datos.
    $this->link->beginTransaction();

    $verificarDatos = $this->verificarDatos($data);
    if($verificarDatos['status']!==0) {
      $this->link->rollBack();
      return $verificarDatos;
    }

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
      $id = $this->link->lastInsertId();
      $this->link->commit();
      return ['status' => 0, 'id' => $id, 'message' => 'El pedido fue editado correctamente.'];
    } else {
      $this->link->rollBack();
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }

  protected function verificarDatos($data) {
    /* Acciones relacionadas con la verificación de la existencia del cliente, y del contacto
    en el cliente */
    $_cliente = new Clientes();
    $_contacto = new Contactos();

    $cliente = $_cliente->obtener($data['clienteID']);
    if($cliente['status']!==0) {
      return $cliente;
      // return ['status' => 2, 'message' => ['Ocurrió un error relacionado con la obtención del cliente.', $cliente]];
    }

    $contacto = $_contacto->obtener($data['contactoID']);
    if($contacto['status']!==0) {
      return $contacto;
      // return ['status' => 2, 'message' => ['Ocurrió un error relacionado con la obtención del contacto.', $contacto]];
    }

    if($contacto['data']['clienteID']!=$cliente['data']['clienteID']) {
      return ['status' => 1, 'message' => 'El contacto ingresado no pertenece a la empresa que ha elegido.'];
    }
    $this->clienteID = $cliente['data']['clienteID'];
    $this->contactoID =  $contacto['data']['contactoID'];
    return ['status' => 0];
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

// $datos = [
//   'pedidoID' => 321,
//   'clienteID' => 18 ,
//   'contactoID' => 11,
//   'categoriaID' => 5,
//   'usuarioID' => 1,
//   'titulo' => 'Título de prueba49',
//   'descripcion' => 'Descripción de prueba49'
// ];

// $pedido = new Pedidos;
// $pedido->verJson();
// echo json_encode($pedido->listar());
// echo json_encode($pedido->ingresar($datos));
// echo json_encode($pedido->editar($datos));
// $resultado = $pedido->ingresar($datos);
// if($resultado['status'] === 0) {
//   echo json_encode($pedido->getPedido($resultado['id']));
// } else {
//   echo json_encode($resultado);
// }
?>
