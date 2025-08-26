<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/funciones.php';

$tituloPagina = 'Catálogo de Libros';
include __DIR__ . '/includes/header.php';

// Obtener y ordenar libros
$orden   = $_GET['orden'] ?? 'titulo';
$libros  = obtenerLibros();
$libros  = ordenarLibros($libros, $orden);
?>

<section class="grid">
  <?php foreach ($libros as $libro): ?>
    <?= mostrarDetallesLibro($libro); ?>
  <?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
