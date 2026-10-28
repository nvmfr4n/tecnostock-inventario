<?php

//variables
$servidor = "localhost";
$usuario = "root"; //usuario inicial por XAMPP (obligatorio)
$contrasena = "";
$base_de_datos = "tecnostock"; 

$conexion_sql = new mysqli($servidor, $usuario, $contrasena, $base_de_datos); //conexión de programa a base de datos SQL

//código para saber si conecta o no
if($conexion_sql -> connect_error)
{
    die("error de conexion". $conexion_sql -> connect_error);  //si hay algún error mostrará error de conexión
}
else
{
    echo "se conectó a la base de datos :)";   //si se conecta mostrará esto
}

?>