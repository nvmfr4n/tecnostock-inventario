<?php
// seguridad de sesión
session_start();
if(!isset($_SESSION['id_usuario'])) { header("Location: index.php"); exit(); }
include 'conexion.php';

// verifica que se haya enviado un id
if (isset($_GET['id']))
{
    $id_recibido = $_GET['id'];
    
    // cambia el estado del producto a inactivo (0) sin eliminarlo de la base
    $consulta = "UPDATE producto SET estado = 0 WHERE id_producto = ?";
    $sentencia = $conexion_sql->prepare($consulta);
    $sentencia->execute([$id_recibido]);
}

// redirige automáticamente a la tabla principal
header("Location: ver_inventario.php");
exit();
?>