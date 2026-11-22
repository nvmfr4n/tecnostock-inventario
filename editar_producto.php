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
    $consulta = "SELECT * FROM producto WHERE id_producto = ? AND estado = 1";
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
        $stock_original = filter_input(INPUT_POST, 'stock_original', FILTER_VALIDATE_INT);
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];

    // valida que no ingresen números negativos
    if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0)
    {
        $mensaje = "<div class='alerta-error'>error: los valores no pueden ser negativos.</div>";
    }
    elseif ($stock_original === null || $stock_original === false || $stock_original < 0)
    {
        $mensaje = "<div class='alerta-error'>vuelve a abrir el formulario antes de guardar.</div>";
    }
    else
    {
        try
        {
            $conexion_sql->beginTransaction();

            // lee el producto y bloquea su fila hasta terminar la edición
            $sentencia_vigente = $conexion_sql->prepare($consulta . ' for update');
            $sentencia_vigente->execute([$id_producto_editar]);
            $producto_vigente = $sentencia_vigente->fetch(PDO::FETCH_ASSOC);
            $sentencia_vigente->closeCursor();

            if (!$producto_vigente)
            {
                $conexion_sql->rollBack();
                $mensaje = "<div class='alerta-error'>el producto ya no está disponible.</div>";
            }
            elseif ((int) $producto_vigente['stock_actual'] !== $stock_original)
            {
                $conexion_sql->rollBack();
                $producto = $producto_vigente;
                $mensaje = "<div class='alerta-error'>el stock cambió mientras editabas. revisa el stock actual y vuelve a ingresar tus cambios.</div>";
            }
            else
            {
                $actualizar = "update producto
                    set nombre = ?, descripcion = ?, precio = ?,
                        stock_actual = ?, stock_minimo = ?, id_categoria = ?
                    where id_producto = ? and estado = 1";
                $sentencia_actualizar = $conexion_sql->prepare($actualizar);
                $sentencia_actualizar->execute([
                    $nombre, $descripcion, $precio, $stock_actual, $stock_minimo,
                    $id_categoria, $id_producto_editar
                ]);
                $sentencia->execute([$id_producto_editar]);
                $producto = $sentencia->fetch(PDO::FETCH_ASSOC);
                $conexion_sql->commit();

                $mensaje = "<div class='alerta-exito'>producto actualizado correctamente.</div>";
            }
        }
        catch (PDOException $error)
        {
            if ($conexion_sql->inTransaction())
            {
                $conexion_sql->rollBack();
            }

            error_log($error->getMessage());
            $mensaje = "<div class='alerta-error'>no se pudo actualizar el producto.</div>";
        }
    }
}
// consulta los nombres y los identificadores para llenar el desplegable
$consulta_categorias = $conexion_sql->query(
    "SELECT id_categoria, nombre FROM categoria ORDER BY nombre"
);
$categorias = $consulta_categorias->fetchAll(PDO::FETCH_ASSOC);
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
            <?php require __DIR__ . '/boton_cerrar_sesion.php'; ?>
        </div>
    </nav>

    <div class="contenedor-panel">
        <h2>Editar Producto: <?php echo htmlspecialchars($producto['codigo']); ?></h2>
        
        <?php echo $mensaje; ?>

        <!-- rellena los input con los datos extraídos de la base de datos -->
        <form method="POST" action="" class="formulario-producto">
            <input type="hidden" name="stock_original"
            value="<?php echo (int) $producto['stock_actual']; ?>">

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

            <label for="id_categoria">Categoría:</label>
            <select id="id_categoria" name="id_categoria" required>
                <?php foreach ($categorias as $categoria): ?>
                    <?php
                    // deja seleccionada la categoría que ya tiene el producto
                    $seleccionada = (int) $categoria['id_categoria'] === (int) $producto['id_categoria'];
                    ?>
                    <option value="<?php echo (int) $categoria['id_categoria']; ?>"
                        <?php echo $seleccionada ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

            <button type="submit" class="btn-guardar">Actualizar Producto</button>
        </form>
    </div>
</body>
</html>