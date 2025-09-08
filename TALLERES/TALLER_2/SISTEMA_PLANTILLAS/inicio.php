<?php
declare(strict_types=1);

$paginaActual = 'inicio';
require_once __DIR__ . '/plantillas/funciones.php';

$tituloPagina = obtenerTituloPagina($paginaActual);
include __DIR__ . '/plantillas/encabezado.php';
?>

<h2>Contenido de la Página de Inicio</h2>
<p>Este es el contenido específico de la página de inicio.</p>

<?php
include __DIR__ . '/plantillas/pie_pagina.php';
