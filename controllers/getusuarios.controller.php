<?php
require('../models/seguimiento-usuarios.models.php');
header('Access-Control-Allow-Origin: *');

$usuarios = new Usuarios;
$res = $usuarios->listar();



echo json_encode($res);


?>
