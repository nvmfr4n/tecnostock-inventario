<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $correo_ingresado = $_POST['correo'];
    $clave_ingresada = $_POST['clave'];

    //Le preguntamos a MySQL si existe una fila en la tabla usuario que tenga ese correo y esa clave
    $consulta = "SELECT * FROM usuario WHERE correo = '$correo_ingresado' AND clave = '$clave_ingresada'";
    
    $resultado = $conexion_sql -> query($consulta);

    //Si el resultado encuentra un registro (1), los datos son correctos. Si encuentra cero (0), estan mal.
    if ($resultado -> num_rows > 0)
    {
        header("Location: inicio.php");
        exit();
    }
    else
    {
        // Mensaje temporal de error
        echo "<script>alert('Error: Correo o contraseña incorrectos.');</script>";
    }
}
?>


<!DOCTYPE html>                                     <!--le avisa al compu que usaremos HTML5 (el más moderno)-->
<html lang = "es">                                  <!--avisa que nuestra página es en español-->
<head>                                              <!--encabezado de la página-->
    <meta charset="UTF-8">                          <!--hace que la pagina pueda leer la letra ñ, tildes y simbolos-->
    <title>Acceso al sistema</title>                <!--texto que aparecerá en la pestaña del navegador, como "YouTube", "Whatsapp", etc-->
    <link rel="stylesheet" href="diseno.css">
</head>                                             <!--esto cierra la configuración-->

<!--todo lo que se coloque desde aquí se verá en el navegador-->
<body>  

    <form action="" method="POST">                              <!--el method="POST" es para que la contraseña esté oculta-->

        <h2>Bienvenido a TecnoStock</h2>
        <p>Por favor, inicia sesión para continuar.</p>
        
        <!--caja para el correo-->
        <label for = "correo">Correo electrónico:</label><br>
        <input type = "email" name="correo" required><br><br>

        <!--caja para la contraseña -->
        <label for="clave">Contraseña:</label><br>
        <input type="password" name="clave" required><br><br>

        <!--botón para enviar-->
        <button type="submit">Ingresar</button>

    </form>

</body>
</html>