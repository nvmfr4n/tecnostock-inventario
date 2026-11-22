<?php
// seguridad de la sesión
session_start();
if(!isset($_SESSION['id_usuario'])) { header("Location: index.php"); exit(); }
include 'conexion.php';

$mensaje = "";

// revisa si el formulario envió información
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];

    // valida que valores numéricos no sean menores a cero
    if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0)
    {
        $mensaje = "<div class='alerta-error'>error: precio y stock no pueden ser negativos.</div>";
    } 
    else
    {
        try
        {
            // prepara la inserción en la base de datos
            $consulta = "INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
            $sentencia = $conexion_sql->prepare($consulta);
            
            // ejecuta guardando los datos reales
            $sentencia->execute([$codigo, $nombre, $descripcion, $precio, $stock_actual, $stock_minimo, $id_categoria]);
            $mensaje = "<div class='alerta-exito'>producto guardado correctamente.</div>";
        }
        catch(PDOException $error)
        {
            // atrapa el error de duplicado y avisa al usuario
            if ($error->getCode() == 23000)
            {
                $mensaje = "<div class='alerta-error'>error: el código de producto ya existe.</div>";
            }
            else
            {
                $mensaje = "<div class='alerta-error'>error de base de datos: " . $error->getMessage() . "</div>";
            }
        }
    }
}
// consulta las categorías para llenar el desplegable
$consulta_categorias = $conexion_sql->query(
    "SELECT id_categoria, nombre FROM categoria ORDER BY nombre"
);
$categorias = $consulta_categorias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body class="body-panel">
    <!-- menú de navegación -->
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
        <h2>Registrar Nuevo Producto</h2>
        
        <?php echo $mensaje; ?>

        <!-- formulario para registrar datos -->
        <form method="POST" action="" class="formulario-producto">
            <label>Código del Producto:</label>
            <input type="text" name="codigo" required>

            <label>Nombre del Producto:</label>
            <input type="text" name="nombre" required>

            <label>Descripción:</label>
            <input type="text" name="descripcion">

            <label>Precio:</label>
            <input type="number" name="precio" required>

            <label>Stock Actual (Inicial):</label>
            <input type="number" name="stock_actual" required>

            <label>Stock Mínimo (Alerta):</label>
            <input type="number" name="stock_minimo" required>

            <label for="id_categoria">Categoría:</label>
            <select id="id_categoria" name="id_categoria" required>
                <option value="">Selecciona una categoría</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo (int) $categoria['id_categoria']; ?>">
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-guardar">Guardar Producto</button>
        </form>
    </div>
</body>
</html>