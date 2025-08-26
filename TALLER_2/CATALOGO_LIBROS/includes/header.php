<?php
declare(strict_types=1);

/**
 * Función de ayuda para escapar HTML.
 */
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna un array simulando una “base de datos” de libros.
 * Cada libro es un array asociativo con campos estándar.
 */
function obtenerLibros(): array {
    return [
        [
            'id'          => 1,
            'titulo'      => 'El nombre del viento',
            'autor'       => 'Patrick Rothfuss',
            'anio'        => 2007,
            'genero'      => 'Fantasía',
            'precio'      => 19.90,
            'isbn'        => '9788401352836',
            'descripcion' => 'La historia de Kvothe, un músico y arcanista brillante.',
        ],
        [
            'id'          => 2,
            'titulo'      => 'Cien años de soledad',
            'autor'       => 'Gabriel García Márquez',
            'anio'        => 1967,
            'genero'      => 'Realismo mágico',
            'precio'      => 14.50,
            'isbn'        => '9788437604947',
            'descripcion' => 'La saga de la familia Buendía en el mítico Macondo.',
        ],
        [
            'id'          => 3,
            'titulo'      => 'Clean Code',
            'autor'       => 'Robert C. Martin',
            'anio'        => 2008,
            'genero'      => 'Tecnología',
            'precio'      => 39.99,
            'isbn'        => '9780132350884',
            'descripcion' => 'Buenas prácticas para escribir código limpio y mantenible.',
        ],
        [
            'id'          => 4,
            'titulo'      => 'Sapiens: De animales a dioses',
            'autor'       => 'Yuval Noah Harari',
            'anio'        => 2011,
            'genero'      => 'Historia',
            'precio'      => 22.75,
            'isbn'        => '9788499924219',
            'descripcion' => 'Un recorrido por la historia y evolución de la humanidad.',
        ],
        [
            'id'          => 5,
            'titulo'      => 'El principito',
            'autor'       => 'Antoine de Saint-Exupéry',
            'anio'        => 1943,
            'genero'      => 'Fábula',
            'precio'      => 9.95,
            'isbn'        => '9780156012195',
            'descripcion' => 'Un viaje poético sobre la amistad, el amor y lo esencial.',
        ],
    ];
}

/**
 * Ordena el array de libros por un criterio dado.
 * Criterios válidos: 'titulo', 'autor', 'anio', 'precio'
 */
function ordenarLibros(array $libros, string $criterio = 'titulo'): array {
    $criterio = in_array($criterio, ['titulo', 'autor', 'anio', 'precio'], true) ? $criterio : 'titulo';

    usort($libros, function (array $a, array $b) use ($criterio): int {
        $va = $a[$criterio];
        $vb = $b[$criterio];

        if (is_string($va) && is_string($vb)) {
            return strnatcasecmp($va, $vb);
        }
        // Comparación numérica para anio/precio
        return $va <=> $vb;
    });

    return $libros;
}

/**
 * Retorna HTML con la “tarjeta” de detalles de un libro.
 */
function mostrarDetallesLibro(array $libro): string {
    $precio = number_format((float)$libro['precio'], 2, '.', ',');
    $titulo = h($libro['titulo']);
    $autor  = h($libro['autor']);
    $anio   = (int)$libro['anio'];
    $genero = h($libro['genero']);
    $isbn   = h($libro['isbn']);
    $desc   = h($libro['descripcion']);

    return <<<HTML
<article class="card">
  <h3 class="card-title">{$titulo}</h3>
  <ul class="meta">
    <li><strong>Autor:</strong> {$autor}</li>
    <li><strong>Año:</strong> {$anio}</li>
    <li><strong>Género:</strong> {$genero}</li>
    <li><strong>ISBN:</strong> {$isbn}</li>
    <li><strong>Precio:</strong> \$ {$precio}</li>
  </ul>
  <p class="desc">{$desc}</p>
</article>
HTML;
}
