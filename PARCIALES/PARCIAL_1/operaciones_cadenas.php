<?php
function contar_palabras_repetidas(string $texto): array
{
    $texto = strtolower(trim($texto));
    $palabras = explode(' ', $texto);

    $conteo = [];

    foreach ($palabras as $palabra) {
        $palabra = trim($palabra);
        $palabra = trim($palabra, ".,;:!¡¿?()[]{}\"'<>-/\\|");

        if ($palabra === '') {
            continue;
        }

        if (!isset($conteo[$palabra])) {
            $conteo[$palabra] = 1;
        } else {
            $conteo[$palabra]++;
        }
    }

    return $conteo;
}

function capitalizar_palabras(string $texto): string
{
    $texto = trim($texto);
    $palabras = explode(' ', $texto);

    $resultado = [];

    foreach ($palabras as $palabra) {
        if ($palabra === '') {
            $resultado[] = '';
            continue;
        }

        $prefijo = '';
        $sufijo  = '';

        $limpia = $palabra;
        $limpia = trim($limpia, ".,;:!¡¿?()[]{}\"'<>-/\\|");

        $start = strpos($palabra, $limpia);
        if ($start === false) $start = 0;
        $end = $start + strlen($limpia);

        $prefijo = substr($palabra, 0, $start);
        $sufijo  = substr($palabra, $end);
        
        $lower = strtolower($limpia);
        $primera = strtoupper(substr($lower, 0, 1));
        $resto = (strlen($lower) > 1) ? substr($lower, 1) : '';

        $resultado[] = $prefijo . $primera . $resto . $sufijo;
    }

    return implode(' ', $resultado);
}
