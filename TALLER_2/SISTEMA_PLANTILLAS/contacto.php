<?php
declare(strict_types=1);

$paginaActual = 'contacto';
require_once __DIR__ . '/plantillas/funciones.php';

$tituloPagina = obtenerTituloPagina($paginaActual);
include __DIR__ . '/plantillas/encabezado.php';
?>

<h2>Contacto</h2>
<p>Puedes escribirnos a <a href="mailto:info@example.com">info@example.com</a> o llamarnos al +507 6000-0000.</p>

<?php
include __DIR__ . '/plantillas/pie_pagina.php';
