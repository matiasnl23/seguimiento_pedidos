<?php
/**
 *  CONEXION
 */
 class SQL{

 	public function conectar(){
     $link = new PDO("mysql:host=localhost;dbname=c0520037_seguimiento;charset=utf8","root","");
 		return $link;
 	}
}

?>
