<?php
session_start();

// comprueba que la solicitud venga del botón de la sesión actual
$token_recibido = $_POST['token_cierre'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['token_cierre'])
    || !is_string($token_recibido)
    || !hash_equals($_SESSION['token_cierre'], $token_recibido))
{
    http_response_code(403);
    exit('Vuelve al panel y usa el botón Cerrar Sesión.');
}

// vacía la sesión y elimina su cookie del navegador
$_SESSION = [];
if (ini_get('session.use_cookies'))
{
    $cookie = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 3600,
        $cookie['path'], $cookie['domain'],
        $cookie['secure'], $cookie['httponly']
    );
}
session_destroy();

header('Location: index.php', true, 303);
exit;