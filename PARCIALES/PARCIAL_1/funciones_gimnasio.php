<?php
function calcular_promocion($antiguedad_meses): int
{
    if ($antiguedad_meses < 3) {
        return 0;
    } elseif ($antiguedad_meses <= 12) {
        return 8;
    } elseif ($antiguedad_meses <= 24) {
        return 12;
    }
    return 20;
}

function calcular_seguro_medico(float $cuota_base): float
{
    return $cuota_base * 0.05;
}

function calcular_cuota_final(float $cuota_base, $descuento_porcentaje, float $seguro_medico): float
{
    $descuento_monto = $cuota_base * ($descuento_porcentaje / 100);
    return $cuota_base - $descuento_monto + $seguro_medico;
}
