<?php
declare(strict_types=1);

// 1) Variables
$nombre_completo = 'Tu Nombre Completo';
$edad            = 25; // número entero
$correo          = 'tu.correo@example.com';
$telefono        = '+507 6000-0000'; // string para mantener formato

// 2) Constante
define('OCUPACION', 'Estudiante');

// 3) Párrafo con toda la info usando echo, print, printf
$br = "<br>\n";

echo "<p>";
echo "Nombre completo: " . $nombre_completo . $br;        // echo + concatenación
print "Edad: " . $edad . " años" . $br;                   // print + concatenación
printf("Correo: %s%s", $correo, $br);                     // printf con placeholder
echo "Teléfono: {$telefono}{$br}";                        // echo con interpolación
echo "Ocupación: " . OCUPACION . $br;                     // echo + constante
echo "</p>";

// 4) var_dump del tipo y valor de cada variable y la constante
echo "<hr>";
echo "<pre>";
var_dump($nombre_completo);
var_dump($edad);
var_dump($correo);
var_dump($telefono);
var_dump(OCUPACION);
echo "</pre>";
