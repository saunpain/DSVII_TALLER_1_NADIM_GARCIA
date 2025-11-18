<?php
session_start();
require_once 'data.php';

if (!isset($_SESSION['username'], $_SESSION['rol'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['rol'] === 'Profesor') {
    header('Location: dashboard_profesor.php');
    exit;
}

$usuarioActual = $_SESSION['username'];
$nota = $calificaciones[$usuarioActual] ?? 'Sin nota registrada';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Estudiante</title>
</head>
<body>
    <h1>Dashboard del Estudiante</h1>
    <p>Usuario: <?php echo htmlspecialchars($usuarioActual); ?></p>

    <h2>Tu calificación</h2>
    <p><?php echo $nota; ?></p>

    <br>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
