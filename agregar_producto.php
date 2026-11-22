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
    $opciones_entero = ['options' => ['min_range' => 0, 'max_range' => 2147483647]];
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_INT, $opciones_entero);
    $stock_actual = filter_input(INPUT_POST, 'stock_actual', FILTER_VALIDATE_INT, $opciones_entero);
    $stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_INT, $opciones_entero);
    $id_categoria = $_POST['id_categoria'];

    // trim() ya quitó los espacios de los extremos del código y del nombre
    if ($codigo === '' || $nombre === '')
    {
        $mensaje = "<div class='alerta-error'>ingresa el código y el nombre del producto.</div>";
    }
    elseif (!is_int($precio) || !is_int($stock_actual) || !is_int($stock_minimo))
    {
        $mensaje = "<div class='alerta-error'>ingresa números enteros desde cero para el precio y el stock.</div>";
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
            <?php require __DIR__ . '/boton_cerrar_sesion.php'; ?>
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
    <script>
    const formulario = document.querySelector('.formulario-producto');
    const campos = [formulario.elements.codigo, formulario.elements.nombre];

    // quita el aviso cuando la persona corrige el campo
    campos.forEach((campo) =>
    {
        campo.addEventListener('input', () => campo.setCustomValidity(''));
    });

    // impide el envío si el código o el nombre tienen solo espacios
    formulario.addEventListener('submit', (evento) =>
    {
        const campoVacio = campos.find((campo) => campo.value.trim() === '');

        if (campoVacio)
        {
            evento.preventDefault();
            campoVacio.setCustomValidity('Escribe un código o un nombre válido en este campo.');
            campoVacio.reportValidity();
        }
    });
</script>
</body>
</html>