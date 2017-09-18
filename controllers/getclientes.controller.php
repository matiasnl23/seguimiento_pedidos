<?php
require('../models/seguimiento-clientes.models.php');
header('Access-Control-Allow-Origin: *');

$cliente = new Clientes;
$res = $cliente->listar();



echo json_encode($res);


?>
