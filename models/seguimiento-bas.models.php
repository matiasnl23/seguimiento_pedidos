<?php
// $basTrabajo['incremental'] = sprintf('%04d', $basTrabajo['incremental']);
require_once('conexion.models.php');

class BAS {
  protected $link;
  protected $bas;

  public function __construct($link = null) {
    if(!$link) {
      $db = new SQL;
      $this->link = $db->conectar();
    } else {
      $this->link = $link;
    }
  }
  public function disponible($area) {
    $q = $this->link->prepare("SELECT
      tagInicial as inicial,
      tagAnual as anual,
      tagMedio as area,
      tagIncremental as incremental
      FROM tblTags
      WHERE tagMedio = :area
      LIMIT 1
    ");

    $q->bindParam(':area', $area, PDO::PARAM_STR);

    if($q->execute()) {
      $this->bas = $q->fetch(PDO::FETCH_ASSOC);
      if($this->bas['anual']<date('y')) {
        if($this->incrementarAnual()['status']!=0) {
          return ['status' => 2, 'message' => 'Se produjo un error al intentar actualizar el año en todos los Tags'];
        }
      }
      return ['status' => 0, 'data' => $this->bas];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }
  public function incrementar() {
    if($this->bas) {
      if($this->bas['incremental']!==null) {
        $basTrabajo = $this->bas;
        ++$basTrabajo['incremental'];
        $q = $this->link->prepare('UPDATE tblTags SET
          tagIncremental = :incremental
          WHERE tagMedio = :area
        ');

        $q->bindParam(':area', $basTrabajo['area'], PDO::PARAM_INT);
        $q->bindParam(':incremental', $basTrabajo['incremental'], PDO::PARAM_INT);

        if($q->execute()) {
          return ['status' => 0, 'data' => $basTrabajo];
        } else {
          return ['status' => 2, 'message' => $q->errorInfo()];
        }
      } else {
        return ['status' => 2, 'message' => ['No se ha encontrado sobre qué tag realizar el incremento.', $this->bas]];
      }
    } else {
      return ['status' => 2, 'message' => 'Antes de realizar esta acción se debe consultar cúal es el tag disponible.'];
    }
  }
  protected function incrementarAnual() {
    $this->bas['incremental'] = 0;
    $this->bas['anual'] = date('y');
    $q = $this->link->prepare("UPDATE tblTags SET
      tagAnual = :anual,
      tagIncremental = 0
      WHERE 1
    ");
    $q->bindParam(':anual', date('y'), PDO::PARAM_INT);
    if($q->execute()) {
      return ['status' => 0, 'message' => 'Se actualizó el año en todos los Tags'];
    } else {
      return ['status' => 2, 'message' => $q->errorInfo()];
    }
  }

}

// $bas = new BAS;
// echo json_encode($bas->disponible('CA'));
// echo json_encode($bas->incrementar());
?>
