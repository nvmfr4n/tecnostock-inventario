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
    else
    {
        try
        {
            $conexion_sql->beginTransaction();

            // lee y bloquea el producto hasta confirmar o revertir el movimiento
            $consulta_stock = $conexion_sql->prepare(
                "select stock_actual from producto where id_producto = ? and estado = 1 for update"
            );
            $consulta_stock->execute([$id_producto]);
            $stock_actual = $consulta_stock->fetchColumn();

            if ($stock_actual === false)
            {
                throw new DomainException('el producto ya no está activo.');
            }

            $stock_actual = (int) $stock_actual;
            $producto['stock_actual'] = $stock_actual;

            // verifica la salida usando el stock vigente dentro de la transacción
            if ($tipo === 'Salida' && $cantidad > $stock_actual)
            {
                throw new DomainException("la salida supera el stock disponible ($stock_actual).");
            }

            if ($tipo === 'Entrada' && $cantidad > 2147483647 - $stock_actual)
            {
                throw new DomainException('la entrada supera el stock máximo permitido.');
            }

            $nuevo_stock = ($tipo === 'Entrada')
                ? $stock_actual + $cantidad
                : $stock_actual - $cantidad;

            // guarda el movimiento y cambia el stock en la misma transacción
            $sentencia_movimiento = $conexion_sql->prepare(
                "insert into movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario)
                values (now(), ?, ?, ?, ?, ?)"
            );
            $sentencia_movimiento->execute([$tipo, $cantidad, $observacion, $id_producto, $_SESSION['id_usuario']]);

            $sentencia_producto = $conexion_sql->prepare(
                "update producto set stock_actual = ? where id_producto = ?"
            );
            $sentencia_producto->execute([$nuevo_stock, $id_producto]);

            $conexion_sql->commit();

            // vuelve al formulario para evitar registrar el movimiento al recargar
            $_SESSION['mensaje_movimiento'] = "<div class='alerta-exito'>movimiento registrado. stock actualizado a: $nuevo_stock.</div>";
            header('Location: movimiento.php?id=' . (int) $id_producto, true, 303);
            exit;
        }
        catch (Exception $error)
        {
            // revierte los cambios si la transacción todavía está abierta
            if ($conexion_sql->inTransaction())
            {
                $conexion_sql->rollBack();
            }

            if ($error instanceof DomainException)
            {
                $mensaje = "<div class='alerta-error'>" . $error->getMessage() . "</div>";
            }
            else
            {
                error_log($error->getMessage());
                $mensaje = "<div class='alerta-error'>no se pudo registrar el movimiento.</div>";
            }
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