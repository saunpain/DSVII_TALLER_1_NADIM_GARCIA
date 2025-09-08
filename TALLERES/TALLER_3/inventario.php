<?php
declare(strict_types=1);

function leerInventario(string $ruta): array
{
    if (!file_exists($ruta)) {
        fwrite(STDERR, "Error: No se encontró el archivo '$ruta'.\n");
        exit(1);
    }

    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        fwrite(STDERR, "Error: No se pudo leer el archivo '$ruta'.\n");
        exit(1);
    }

    $datos = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        fwrite(STDERR, "Error: JSON inválido en '$ruta' -> " . json_last_error_msg() . "\n");
        exit(1);
    }

    if (!is_array($datos)) {
        fwrite(STDERR, "Error: La estructura del JSON debe ser un array de productos.\n");
        exit(1);
    }

    $productos = array_map(function ($item) {
        $nombre   = isset($item['nombre']) ? (string)$item['nombre'] : '';
        $precio   = isset($item['precio']) ? (float)$item['precio'] : 0.0;
        $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;

        return [
            'nombre'   => $nombre,
            'precio'   => $precio,
            'cantidad' => $cantidad,
        ];
    }, $datos);

    return $productos;
}

function ordenarInventarioPorNombre(array $productos): array
{
    usort($productos, function ($a, $b) {
        $na = mb_strtolower($a['nombre'] ?? '');
        $nb = mb_strtolower($b['nombre'] ?? '');
        return $na <=> $nb;
    });

    return $productos;
}

function mostrarResumenInventario(array $productos): void
{
    if (empty($productos)) {
        echo "Inventario vacío.\n";
        return;
    }

    $anchoNombre   = max(6, ...array_map(fn($p) => mb_strlen((string)$p['nombre']), $productos));
    $anchoPrecio   = 10;
    $anchoCantidad = max(8, ...array_map(fn($p) => mb_strlen((string)$p['cantidad']), $productos));

    $linea = str_repeat('-', $anchoNombre + $anchoPrecio + $anchoCantidad + 8);

    printf("%s\n", $linea);
    printf("| %-" . $anchoNombre . "s | %-" . $anchoPrecio . "s | %-" . $anchoCantidad . "s |\n",
        'Nombre', 'Precio', 'Cantidad'
    );
    printf("%s\n", $linea);

    foreach ($productos as $p) {
        printf(
            "| %-" . $anchoNombre . "s | % " . $anchoPrecio . "s | % " . $anchoCantidad . "d |\n",
            (string)$p['nombre'],
            formatearMoneda((float)$p['precio']),
            (int)$p['cantidad']
        );
    }
    printf("%s\n", $linea);
}

function calcularValorTotalInventario(array $productos): float
{
    $valores = array_map(fn($p) => ((float)$p['precio']) * ((int)$p['cantidad']), $productos);
    return array_sum($valores);
}

function generarInformeStockBajo(array $productos, int $umbral = 5): array
{
    return array_values(array_filter($productos, fn($p) => ((int)$p['cantidad']) < $umbral));
}

function formatearMoneda(float $monto): string
{
    return number_format($monto, 2, '.', '');
}

function titulo(string $texto): void
{
    echo "\n=== $texto ===\n";
}

function main(array $argv): void
{
    $umbral = 5;
    if (isset($argv[1]) && ctype_digit($argv[1])) {
        $umbral = max(0, (int)$argv[1]);
    }

    $rutaJSON = __DIR__ . DIRECTORY_SEPARATOR . 'inventario.json';

    $inventario = leerInventario($rutaJSON);

    $inventarioOrdenado = ordenarInventarioPorNombre($inventario);

    titulo("Resumen del inventario (ordenado A-Z)");
    mostrarResumenInventario($inventarioOrdenado);

    $total = calcularValorTotalInventario($inventarioOrdenado);
    titulo("Valor total del inventario");
    echo "Total: $" . formatearMoneda($total) . "\n";

    $bajoStock = generarInformeStockBajo($inventarioOrdenado, $umbral);
    titulo("Productos con stock bajo (menos de $umbral unidades)");
    if (empty($bajoStock)) {
        echo "No hay productos con stock bajo.\n";
    } else {
        mostrarResumenInventario($bajoStock);
    }
}

main($argv);
