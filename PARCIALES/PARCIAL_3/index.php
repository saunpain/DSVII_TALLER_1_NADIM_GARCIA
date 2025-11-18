<?php
session_start();
require_once 'data.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (strlen($username) < 3) {
        $errores[] = 'El nombre de usuario debe tener al menos 3 caracteres.';
    }
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $errores[] = 'El nombre de usuario solo puede contener letras y números.';
    }

    if (strlen($password) < 5) {
        $errores[] = 'La contraseña debe tener al menos 5 caracteres.';
    }

    if (empty($errores)) {
        $usuarioEncontrado = null;

        foreach ($usuarios as $u) {
            if ($u['username'] === $username && $u['password'] === $password) {
                $usuarioEncontrado = $u;
                break;
            }
        }

        if ($usuarioEncontrado) {
            $_SESSION['username'] = $usuarioEncontrado['username'];
            $_SESSION['rol']      = $usuarioEncontrado['rol'];

            if ($usuarioEncontrado['rol'] === 'Profesor') {
                header('Location: dashboard_profesor.php');
                exit;
            } else {
                header('Location: dashboard_estudiante.php');
                exit;
            }
        } else {
            $errores[] = 'Credenciales incorrectas.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Parcial 3</title>
</head>
<body>
    <h1>Inicio de sesión</h1>

    <?php if (!empty($errores)): ?>
        <ul style="color:red;">
            <?php foreach ($errores as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="index.php">
        <label>
            Nombre de usuario:
            <input type="text" name="username" required>
        </label>
        <br><br>
        <label>
            Contraseña:
            <input type="password" name="password" required>
        </label>
        <br><br>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
