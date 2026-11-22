<?php
// genera un token para reconocer las solicitudes de cierre de esta sesión
$_SESSION['token_cierre'] ??= bin2hex(random_bytes(32));
?>
<form method="post" action="cerrar_sesion.php"
      style="display: inline; background: none; padding: 0; box-shadow: none;">
    <input type="hidden" name="token_cierre"
           value="<?php echo htmlspecialchars($_SESSION['token_cierre'], ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn-salir" style="width: auto; margin-left: 20px;">
        Cerrar Sesión
    </button>
</form>