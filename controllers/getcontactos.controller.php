<?php
require('../models/seguimiento-contactos.models.php');
header('Access-Control-Allow-Origin: *');



$contacto = new Contactos;
if($contacto->getAll()) {
  echo json_encode($contacto->print());
} else {
  echo json_encode($contacto->printError());
}
?>
