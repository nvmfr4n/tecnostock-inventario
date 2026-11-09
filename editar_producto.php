<?php
// seguridad de sesión
session_start();
if(!isset($_SESSION['id_usuario'])) { header("Location: index.php"); exit(); }
include 'conexion.php';

$mensaje = "";

// recibe el id desde la url de forma segura
if (isset($_GET['id']))
{
    $id_producto_editar = $_GET['id'];
    
    // busca el producto actual para llenar el formulario
    $consulta = "SELECT * FROM producto WHERE id_producto = ?";
    $sentencia = $conexion_sql->prepare($consulta);
    $sentencia->execute([$id_producto_editar]);
    
    $producto = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto)
    {
        die("producto no encontrado.");
    }
}
else
{
    die("no se seleccionó ningún producto para editar.");
}

// procesa los datos nuevos cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];

    // valida que no ingresen números negativos
    if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0)
    {
        $mensaje = "<div class='alerta-error'>error: los valores no pueden ser negativos.</div>";
    }
    else
    {
        // actualiza el registro en la base de datos
        $actualizar = "UPDATE producto SET 
                        nombre = ?, descripcion = ?, precio = ?, 
                        stock_actual = ?, stock_minimo = ?, id_categoria = ? 
                      WHERE id_producto = ?";
        
        $sentencia_actualizar = $conexion_sql->prepare($actualizar);
        
        try
        {
            $sentencia_actualizar->execute([$nombre, $descripcion, $precio, $stock_actual, $stock_minimo, $id_categoria, $id_producto_editar]);
            $mensaje = "<div class='alerta-exito'>producto actualizado correctamente.</div>";
            
            // actualiza los datos en pantalla inmediatamente
            $producto['nombre'] = $nombre;
            $producto['descripcion'] = $descripcion;
            $producto['precio'] = $precio;
            $producto['stock_actual'] = $stock_actual;
            $producto['stock_minimo'] = $stock_minimo;
            $producto['id_categoria'] = $id_categoria;
        }
        catch (PDOException $error)
        {
            $mensaje = "<div class='alerta-error'>error al actualizar: " . $error->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body class="body-panel">
    <nav class="menu-superior">
        <div class="logo-menu">TecnoStock</div>
        <div class="enlaces-menu">
            <a href="inicio.php">Inicio</a>
            <a href="ver_inventario.php">Ver Inventario</a>
            <a href="agregar_producto.php">Agregar Producto</a>
            <a href="index.php" class="btn-salir">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="contenedor-panel">
        <h2>Editar Producto: <?php echo htmlspecialchars($producto['codigo']); ?></h2>
        
        <?php echo $mensaje; ?>

        <!-- rellena los input con los datos extraídos de la base de datos -->
        <form method="POST" action="" class="formulario-producto">
            <label>Nombre del Producto:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

            <label>Descripción:</label>
            <input type="text" name="descripcion" value="<?php echo htmlspecialchars($producto['descripcion']); ?>">

            <label>Precio:</label>
            <input type="number" name="precio" value="<?php echo $producto['precio']; ?>" required>

            <label>Stock Actual:</label>
            <input type="number" name="stock_actual" value="<?php echo $producto['stock_actual']; ?>" required>

            <label>Stock Mínimo (Alerta):</label>
            <input type="number" name="stock_minimo" value="<?php echo $producto['stock_minimo']; ?>" required>

            <label>ID Categoría:</label>
            <input type="number" name="id_categoria" value="<?php echo $producto['id_categoria']; ?>" required>

            <button type="submit" class="btn-guardar">Actualizar Producto</button>
        </form>
    </div>
</body>
</html>