<?php

// protege la página verificando si el usuario inició sesión
session_start();
if(!isset($_SESSION['id_usuario']))
{
    header("Location: index.php"); exit();
}
include 'conexion.php';

// cuenta los productos activos y los que están por debajo de su stock mínimo
$consulta_resumen = $conexion_sql->query
(
    "SELECT COUNT(*) AS total_productos,
            COUNT(CASE WHEN stock_actual < stock_minimo THEN 1 END) AS alertas_stock
     FROM producto
     WHERE estado = 1"
);
$resumen = $consulta_resumen->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body class="body-panel">
    <!-- menú de navegación principal -->
    <nav class="menu-superior">
        <div class="logo-menu">TecnoStock</div>
        <div class="enlaces-menu">
            <a href="inicio.php">Inicio</a>
            <a href="ver_inventario.php">Ver Inventario</a>
            <a href="agregar_producto.php">Agregar Producto</a>
            <a href="index.php" class="btn-salir">Cerrar Sesión</a>
        </div>
    </nav>

    <!-- contenido del panel -->
    <div class="contenedor-panel">
        <h2>Bienvenido al Panel de Administración</h2>
        <p>Selecciona una opción en el menú superior para gestionar el inventario.</p>
        
        <div class="tarjetas-resumen">
            <div class="tarjeta">
                <h3>Total Productos</h3>
                <p class="numero-grande"><?php echo (int) $resumen['total_productos']; ?></p>
            </div>
            <div class="tarjeta">
                <h3>Alertas de Stock</h3>
                <p class="numero-grande"><?php echo (int) $resumen['alertas_stock']; ?></p>
            </div>
        </div>
    </div>
</body>
</html>