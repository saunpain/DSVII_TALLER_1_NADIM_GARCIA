<?php
declare(strict_types=1);

function obtenerTituloPagina(string $pagina): string {
    $titulos = [
        'inicio'         => 'Página de Inicio',
        'sobre_nosotros' => 'Sobre Nosotros',
        'contacto'       => 'Contáctanos',
    ];
    return isset($titulos[$pagina]) ? $titulos[$pagina] : 'Página Desconocida';
}

function generarMenu(string $paginaActual): string {
    $menu = [
        'inicio'         => 'Inicio',
        'sobre_nosotros' => 'Sobre Nosotros',
        'contacto'       => 'Contacto',
    ];

    $html = '<nav><ul>';
    foreach ($menu as $pagina => $titulo) {
        $clase = ($pagina === $paginaActual) ? ' class="activo"' : '';
        // Nota: comillas escapadas en href y variables interpoladas de forma segura
        $html .= "<li><a href=\"{$pagina}.php\"{$clase}>{$titulo}</a></li>";
    }
    $html .= '</ul></nav>';
    return $html;
}
