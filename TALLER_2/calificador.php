<?php
declare(strict_types=1);

// Declara la calificación (0–100)
$calificacion = 86; // cámbiala para probar

// Validación simple
if ($calificacion < 0 || $calificacion > 100) {
    die("La calificación debe estar entre 0 y 100.<br>\n");
}

// if-elseif-else para determinar la letra
if ($calificacion >= 90) {
    $letra = 'A';
} elseif ($calificacion >= 80) {
    $letra = 'B';
} elseif ($calificacion >= 70) {
    $letra = 'C';
} elseif ($calificacion >= 60) {
    $letra = 'D';
} else {
    $letra = 'F';
}

// Mensaje principal + ternario (Aprobado/Reprobado)
echo "Tu calificación es {$letra} — " . ($calificacion >= 60 ? "Aprobado" : "Reprobado") . "<br>\n";

// Mensaje adicional con switch
switch ($letra) {
    case 'A':
        echo "Excelente trabajo<br>\n";
        break;
    case 'B':
        echo "Buen trabajo<br>\n";
        break;
    case 'C':
        echo "Trabajo aceptable<br>\n";
        break;
    case 'D':
        echo "Necesitas mejorar<br>\n";
        break;
    case 'F':
        echo "Debes esforzarte más<br>\n";
        break;
}
