<?php
declare(strict_types=1);

/**
 * 1) Patrón de triángulo rectángulo con for (5 filas)
 */
$filas = 5;
for ($i = 1; $i <= $filas; $i++) {
    echo str_repeat('*', $i) . "<br>\n";
}

echo "<hr>\n";

/**
 * 2) While: números del 1 al 20 mostrando solo impares
 *    (usamos if / elseif y continue)
 */
$n = 1;
while ($n <= 20) {
    if ($n % 2 === 0) {
        // Par: no se muestra. Incrementamos y saltamos al siguiente ciclo.
        $n++;
        continue;
    } elseif ($n % 2 !== 0) {
        // Impar: lo mostramos
        echo $n . "<br>\n";
    }
    // Incremento común al final del ciclo
    $n++;
}

echo "<hr>\n";

/**
 * 3) Do-while: contador regresivo 10 → 1, saltando el 5
 *    (usamos if y continue)
 */
$c = 10;
do {
    if ($c === 5) {
        // Saltar el 5
        $c--;
        continue;
    }
    echo $c . "<br>\n";
    $c--;
} while ($c >= 1);
