<?php
session_start();
require_once 'data.php';

if (!isset($_SESSION['username'], $_SESSION['rol'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['rol'] !== 'Profesor') {
    header('Location: dashboard_estudiante.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Profesor</title>
</head>
<body>
    <h1>Dashboard del Profesor</h1>
    <p>Usuario: <?php echo htmlspecialchars($_SESSION['username']); ?></p>

    <h2>Listado de estudiantes y sus calificaciones</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Estudiante</th>
            <th>Calificación</th>
        </tr>
        <?php foreach ($usuarios as $u): ?>
            <?php if ($u['rol'] === 'Estudiante'): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td>
                        <?php
                        $user = $u['username'];
                        echo isset($calificaciones[$user]) ? $calificaciones[$user] : 'Sin nota';
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
