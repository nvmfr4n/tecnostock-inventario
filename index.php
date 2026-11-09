<?php

// inicia la sesión para recordar al usuario
session_start();
include 'conexion.php';

$mensaje = "";

// verifica si el usuario presionó el botón enviar por post
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];

    // prepara la consulta evitando inyección sql con los signos de interrogación
    $consulta = "SELECT * FROM usuario WHERE correo = ? AND clave = ? AND estado = 1";
    $sentencia = $conexion_sql->prepare($consulta);

    // reemplaza los signos de interrogación con los datos del formulario
    $sentencia->execute([$correo, $clave]);
    
    // extrae los datos del usuario si las credenciales son correctas
    $usuario_encontrado = $sentencia->fetch(PDO::FETCH_ASSOC);

    if ($usuario_encontrado)
    {
        // guarda id y nombre en la sesión para darle acceso al panel
        $_SESSION['id_usuario'] = $usuario_encontrado['id_usuario'];
        $_SESSION['nombre'] = $usuario_encontrado['nombre'];

        // redirige al usuario a la página de inicio
        header("Location: inicio.php");
        exit();
    }
    else
    {
        $mensaje = "<div class='alerta-error'>error: correo o contraseña incorrectos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso - TecnoStock</title>
    <link rel="stylesheet" href="diseno.css">
</head>
<body>  
    <form action="" method="POST">
        <h2>Bienvenido a TecnoStock</h2>
        <p>Por favor, inicia sesión para continuar.</p>
        <?php echo $mensaje; ?>
        <label>Correo electrónico:</label><br>
        <input type="email" name="correo" required><br><br>
        <label>Contraseña:</label><br>
        <input type="password" name="clave" required><br><br>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>