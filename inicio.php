<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body class="body-panel">
    <!-- Menú de navegación superior -->
    <nav class="menu-superior">
        <div class="logo-menu">TecnoStock</div>
        <div class="enlaces-menu">
            <a href="inicio.php">Inicio</a>
            <a href="#">Ver Inventario</a>
            <a href="#">Agregar Producto</a>
            <a href="index.php" class="btn-salir">Cerrar Sesión</a>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="contenedor-panel">
        <h2>Bienvenido al Panel de Administración</h2>
        <p>Selecciona una opción en el menú superior para gestionar el inventario.</p>
        
        <div class="tarjetas-resumen">
            <div class="tarjeta">
                <h3>Total Productos</h3>
                <p class="numero-grande">0</p>
            </div>
            <div class="tarjeta">
                <h3>Alertas de Stock</h3>
                <p class="numero-grande">0</p>
            </div>
        </div>
    </div>
</body>
</html>