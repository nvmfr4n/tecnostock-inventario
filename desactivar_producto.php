<?php
session_start();

if (!isset($_SESSION['id_usuario']))
{
    header('Location: index.php', true, 303);
    exit;
}

// comprueba que la solicitud venga del formulario del inventario
$token_recibido = $_POST['token_desactivar'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['token_desactivar'])
    || !is_string($token_recibido)
    || !hash_equals($_SESSION['token_desactivar'], $token_recibido))
{
    http_response_code(403);
    exit('Vuelve al inventario y usa el botón Desactivar.');
}

// comprueba que el identificador sea un entero positivo
$id_recibido = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);

if (!$id_recibido || $id_recibido < 1)
{
    http_response_code(400);
    exit('El producto indicado no es válido.');
}

include 'conexion.php';

// conserva el registro y cambia su estado a inactivo
$consulta = 'UPDATE producto SET estado = 0 WHERE id_producto = ? AND estado = 1';
$sentencia = $conexion_sql->prepare($consulta);
$sentencia->execute([$id_recibido]);

header('Location: ver_inventario.php', true, 303);
exit;