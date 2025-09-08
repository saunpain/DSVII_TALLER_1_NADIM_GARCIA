<?php

require_once __DIR__ . '/funciones_gimnasio.php';

$membresias = [
    'basica' => 80.00,
    'premium' => 120.00,
    'vip' => 180.00,
    'familiar' => 250.00,
    'corporativa' => 300.00,
];

$miembros = [
    'Juan Pérez' => ['tipo' => 'premium', 'antiguedad' => 15],
    'Ana García' => ['tipo' => 'basica', 'antiguedad' => 2],
    'Carlos López' => ['tipo' => 'vip', 'antiguedad' => 30],
    'María Rodríguez' => ['tipo' => 'familiar', 'antiguedad' => 8],
    'Luis Martínez' => ['tipo' => 'corporativa', 'antiguedad' => 18],
];

$resultados = [];
foreach ($miembros as $nombre => $info) {
    $tipo = $info['tipo'];
    $ant = $info['antiguedad'];

    $cuota_base = $membresias[$tipo];

    $promo_pct = calcular_promocion($ant);
    $seguro = calcular_seguro_medico($cuota_base);
    $cuota_final = calcular_cuota_final($cuota_base, $promo_pct, $seguro);

    $descuento_monto = $cuota_base * ($promo_pct / 100);

    $resultados[] = [
        'nombre'          => $nombre,
        'tipo'            => $tipo,
        'antiguedad'      => $ant,
        'cuota_base'      => $cuota_base,
        'promo_pct'       => $promo_pct,
        'descuento_monto' => $descuento_monto,
        'seguro'          => $seguro,
        'cuota_final'     => $cuota_final,
    ];
}

function money($n) { return '$ ' . number_format($n, 2, '.', ','); }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen de Membresías - Gimnasio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { color-scheme: light dark; }
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; }
        h1 { margin-bottom: 6px; }
        p  { margin-top: 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; color: black}
        span { color: black; }
        .tag { padding: 2px 8px; border-radius: 999px; background: #eef; font-size: 12px; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
        tfoot td { font-weight: 600; }
    </style>
</head>
<body>
    <h1>Resumen de Membresías</h1>
    <p>Cuotas calculadas con promoción por antigüedad y un cargo de seguro médico del 5%.</p>

    <table>
        <thead>
            <tr>
                <th>Miembro</th>
                <th>Tipo</th>
                <th>Antigüedad (meses)</th>
                <th>Cuota base</th>
                <th>Descuento</th>
                <th>Seguro médico (5%)</th>
                <th>Cuota final</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($resultados as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><span class="tag"><?php echo htmlspecialchars($r['tipo'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                <td class="mono"><?php echo (int)$r['antiguedad']; ?></td>
                <td class="mono"><?php echo money($r['cuota_base']); ?></td>
                <td class="mono">
                    <?php echo $r['promo_pct']; ?>% (<?php echo money($r['descuento_monto']); ?>)
                </td>
                <td class="mono"><?php echo money($r['seguro']); ?></td>
                <td class="mono"><?php echo money($r['cuota_final']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
