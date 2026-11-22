<?php
// seguridad de sesión
session_start();
if(!isset($_SESSION['id_usuario'])) { header("Location: index.php"); exit(); }
include 'conexion.php';

$texto_ingresado = "";
$termino_busqueda = "";

// revisa si el usuario usó la barra de búsqueda
if (isset($_GET['busqueda']) && $_GET['busqueda'] != "")
{
    $texto_ingresado = $_GET['busqueda']; 

    // agrega los porcentajes necesarios para la búsqueda en sql
    $termino_busqueda = '%' . $texto_ingresado . '%'; 
    
    // une cada producto con su categoría para buscar por el nombre de la categoría
    $consulta = "SELECT producto.*, categoria.nombre AS nombre_categoria
                FROM producto
                INNER JOIN categoria
                    ON producto.id_categoria = categoria.id_categoria
                WHERE producto.estado = 1
                    AND (producto.codigo LIKE ?
                        OR producto.nombre LIKE ?
                        OR categoria.nombre LIKE ?)";
    $sentencia = $conexion_sql->prepare($consulta);
    $sentencia->execute([$termino_busqueda, $termino_busqueda, $termino_busqueda]);
}
else
{
    // obtiene los productos activos junto con el nombre de su categoría
    $consulta = "SELECT producto.*, categoria.nombre AS nombre_categoria
                FROM producto
                INNER JOIN categoria
                    ON producto.id_categoria = categoria.id_categoria
                WHERE producto.estado = 1";
    $sentencia = $conexion_sql->prepare($consulta);
    $sentencia->execute();
}

// guarda los productos encontrados para mostrarlos en la tabla
$productos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
$_SESSION['token_desactivar'] ??= bin2hex(random_bytes(32));
$token_desactivar = $_SESSION['token_desactivar'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body class="body-panel">
    <nav class="menu-superior">
        <div class="logo-menu">TecnoStock</div>
        <div class="enlaces-menu">
            <a href="inicio.php">Inicio</a>
            <a href="ver_inventario.php">Ver Inventario</a>
            <a href="agregar_producto.php">Agregar Producto</a>
        <?php require __DIR__ . '/boton_cerrar_sesion.php'; ?>        </div>
    </nav>

    <div class="contenedor-panel">
        <h2>Listado de Productos</h2>
        <p>Aquí puedes consultar y buscar todos los productos registrados en el sistema.</p>
        
        <!-- formulario buscador por método get para que se vea en la url -->
        <form method="GET" action="ver_inventario.php" class="formulario-buscador">
            <input type="text" name="busqueda" placeholder="Buscar por código, nombre o categoría..." value="<?php echo htmlspecialchars($texto_ingresado); ?>">
            <button type="submit" class="btn-buscar">Buscar</button>
            <a href="ver_inventario.php" class="btn-limpiar">Mostrar Todos</a>
        </form>
        
        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- verifica si encontró algún producto -->
                <?php if (count($productos) > 0): ?>
                    <!-- recorre los productos uno por uno -->
                    <?php foreach ($productos as $fila): 
                    // asigna una clase css roja si el stock es bajo
                    $clase_alerta = ($fila['stock_actual'] < $fila['stock_minimo']) ? "alerta-stock" : "";
                    ?>
                    <tr class='<?php echo $clase_alerta; ?>'>
                        <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($fila['nombre_categoria']); ?></td>
                        <td><?php echo htmlspecialchars($fila['descripcion']); ?></td>
                        <td>$<?php echo number_format($fila['precio'], 0, ',', '.'); ?></td>
                        <td><?php echo $fila['stock_actual']; ?></td>
                        <td><?php echo $fila['stock_minimo']; ?></td>
                        <td>
                            <!-- botones de acciones que envían el id_producto por url -->
                            <a href='movimiento.php?id=<?php echo $fila['id_producto']; ?>' style='background:#198754;color:white;padding:5px;border-radius:4px;text-decoration:none;font-size:14px;'>Movimiento</a>
                            <a href='editar_producto.php?id=<?php echo $fila['id_producto']; ?>' class='btn-editar'>Editar</a> 
                            <form method="post" action="desactivar_producto.php"
                                onsubmit="return confirm('¿Desactivar este producto?');"
                                style="display: inline-block; width: auto; padding: 0; background: transparent; box-shadow: none;">
                            <input type="hidden" name="id_producto"
                                value="<?php echo (int) $fila['id_producto']; ?>">
                            <input type="hidden" name="token_desactivar"
                                value="<?php echo htmlspecialchars($token_desactivar, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn-desactivar" style="width: auto;">Desactivar</button>
                        </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- mensaje si la tabla o búsqueda está vacía -->
                    <tr><td colspan='8' style='text-align:center;'>no se encontraron productos activos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>