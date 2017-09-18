<?php
require('../models/seguimiento-pedidos.models.php');
header('Access-Control-Allow-Origin: *');

$pedido = new Pedidos;
$res = $pedido->listar();



echo json_encode($res);


?>
