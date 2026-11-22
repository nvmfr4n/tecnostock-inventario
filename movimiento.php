<?php
// seguridad de sesión
session_start();
if(!isset($_SESSION['id_usuario'])) { header("Location: index.php"); exit(); }
include 'conexion.php';

// muestra una sola vez el aviso del movimiento anterior
$mensaje = $_SESSION['mensaje_movimiento'] ?? '';
unset($_SESSION['mensaje_movimiento']);

// asegura que llegue un id válido
if (!isset($_GET['id'])) die("id no proporcionado");
$id_producto = $_GET['id'];

// extrae la información del producto para mostrar su nombre y stock actual
$sentencia = $conexion_sql->prepare("SELECT * FROM producto WHERE id_producto = ? AND estado = 1");
$sentencia->execute([$id_producto]);
$producto = $sentencia->fetch(PDO::FETCH_ASSOC);

// detiene la página si el producto no existe o está desactivado
if (!$producto)
{
    http_response_code(404);
    exit('El producto no existe o está desactivado.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $tipo = $_POST['tipo'] ?? '';
    $cantidad = filter_var($_POST['cantidad'] ?? '', FILTER_VALIDATE_INT);
    $observacion = $_POST['observacion'] ?? '';

// valida el tipo y exige una cantidad entera mayor que cero
    if (!in_array($tipo, ['Entrada', 'Salida'], true))
    {
        $mensaje = "<div class='alerta-error'>Selecciona Entrada o Salida.</div>";
    }
    elseif ($cantidad === false || $cantidad < 1)
    {
        $mensaje = "<div class='alerta-error'>La cantidad debe ser un número entero mayor que cero.</div>";
    }
    elseif ($tipo == 'Salida' && $cantidad > $producto['stock_actual'])
    {
        // previene vender o sacar más de lo que hay
        $mensaje = "<div class='alerta-error'>error: la salida no puede superar el stock actual (stock: {$producto['stock_actual']}).</div>";
    }
    else
    {
        try 
        {
            // inicia una transacción: si un paso falla, revierte todo para no dañar los datos
            $conexion_sql->beginTransaction();

            // paso 1: registra el historial de movimiento
            $sentencia_movimiento = $conexion_sql->prepare("INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES (NOW(), ?, ?, ?, ?, ?)");
            $sentencia_movimiento->execute([$tipo, $cantidad, $observacion, $id_producto, $_SESSION['id_usuario']]);

            // paso 2: recalcula y guarda el nuevo stock general del producto
            $nuevo_stock = ($tipo == 'Entrada') ? ($producto['stock_actual'] + $cantidad) : ($producto['stock_actual'] - $cantidad);
            $sentencia_producto = $conexion_sql->prepare("UPDATE producto SET stock_actual = ? WHERE id_producto = ?");
            $sentencia_producto->execute([$nuevo_stock, $id_producto]);

            // confirma la transacción y aplica los cambios reales
            $conexion_sql->commit();
            // guarda el aviso y vuelve a abrir la página sin reenviar el formulario
            $_SESSION['mensaje_movimiento'] = "<div class='alerta-exito'>Movimiento registrado. Stock actualizado a: $nuevo_stock.</div>";
            header('Location: movimiento.php?id=' . (int) $id_producto, true, 303);
            exit;
        }
        catch (Exception $error) 
        {
            // revierte la transacción en caso de error
            $conexion_sql->rollBack();
            $mensaje = "<div class='alerta-error'>error: " . $error->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimiento - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body class="body-panel">
    <nav class="menu-superior">
        <div class="logo-menu">TecnoStock</div>
        <div class="enlaces-menu">
            <a href="inicio.php">Inicio</a>
            <a href="ver_inventario.php">Ver Inventario</a>
            <?php require __DIR__ . '/boton_cerrar_sesion.php'; ?>
        </div>
    </nav>
    <div class="contenedor-panel">
        <h2>Registrar Movimiento: <?php echo htmlspecialchars($producto['nombre']); ?></h2>
        <p>Stock actual: <strong><?php echo $producto['stock_actual']; ?></strong></p>
        <?php echo $mensaje; ?>
        
        <form method="POST" action="" class="formulario-producto">
            <label>Tipo de Movimiento:</label>
            <select name="tipo" style="padding: 10px; margin-bottom: 15px; width:100%; border: 1px solid #ccc; border-radius: 5px;">
                <option value="Entrada">Entrada (aumentar stock)</option>
                <option value="Salida">Salida (disminuir stock)</option>
            </select>

            <label>Cantidad:</label>
            <input type="number" name="cantidad" min="1" required>

            <label>Observación (Opcional):</label>
            <input type="text" name="observacion">

            <button type="submit" class="btn-guardar">Guardar Movimiento</button>
        </form>
    </div>
</body>
</html>