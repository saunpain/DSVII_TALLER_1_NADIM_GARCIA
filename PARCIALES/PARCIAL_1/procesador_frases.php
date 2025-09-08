<?php

require_once __DIR__ . '/operaciones_cadenas.php';

$frases = [
    "tres por tres es nueve",
    "  Hola   mundo, hola Mundo!  ",
    "Aprender PHP, es muy sencillo; php es muy sencillo",
    "Este lenguaje va rápido? si, va muy muy rápido."
];

function formato_conteo(array $conteo): string {
    $pares = [];
    foreach ($conteo as $palabra => $cantidad) {
        $pares[] = $palabra . ': ' . $cantidad;
    }
    return implode(', ', $pares);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesador de Frases</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding: 24px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 10px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        code { background: #f6f8fa; padding: 2px 4px; border-radius: 4px; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
    </style>
</head>
<body>
    <h1>Procesador de Frases</h1>
    <p>Se aplican las funciones <code>contar_palabras_repetidas</code> y <code>capitalizar_palabras</code> a cada frase.</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Frase original</th>
                <th>Frase capitalizada</th>
                <th>Conteo de palabras (minúsculas)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($frases as $i => $frase): 
            $capitalizada = capitalizar_palabras($frase);
            $conteo = contar_palabras_repetidas($frase);
        ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td class="mono"><?php echo htmlspecialchars($frase, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="mono"><?php echo htmlspecialchars($capitalizada, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="mono"><?php echo htmlspecialchars(formato_conteo($conteo), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
