<?php

// datos para conectar a la base de datos local
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "tecnostock"; 

try
{
    // conecta usando pdo para mayor seguridad y acepta caracteres especiales
    $conexion_sql = new PDO("mysql:host=$servidor;dbname=$base_de_datos;charset=utf8", $usuario,$contrasena);

    // configura pdo para que muestre excepciones si ocurre algún error
    $conexion_sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}
catch(PDOException $error)
{
    // si falla la conexión, detiene la ejecución y muestra el motivo
    die("error de conexión: " . $error->getMessage());
}
?>