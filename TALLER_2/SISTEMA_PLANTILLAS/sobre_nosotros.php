<?php
declare(strict_types=1);

$paginaActual = 'sobre_nosotros';
require_once __DIR__ . '/plantillas/funciones.php';

$tituloPagina = obtenerTituloPagina($paginaActual);
include __DIR__ . '/plantillas/encabezado.php';
?>

<h2>Acerca de Nosotros</h2>
<p>Somos una organización dedicada a ofrecer el mejor contenido de ejemplo para tu taller.</p>

<?php
include __DIR__ . '/plantillas/pie_pagina.php';
