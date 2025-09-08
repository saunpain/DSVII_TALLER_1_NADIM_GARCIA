<?php
// Este archivo asume que $tituloPagina (string) y $paginaActual (string)
// ya están definidos en la página que lo incluye, y que funciones.php fue cargado.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        nav ul { list-style-type: none; padding: 0; }
        nav ul li { display: inline; margin-right: 10px; }
        .activo { font-weight: bold; }
        header { margin-bottom: 20px; }
    </style>
</head>
<body>
    <header>
        <h1><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></h1>
        <?= generarMenu($paginaActual); ?>
    </header>
    <main>
