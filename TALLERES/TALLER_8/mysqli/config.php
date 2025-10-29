<?php
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'biblioteca_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli {
    static $cn = null;
    if ($cn === null) {
        $cn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $cn->set_charset('utf8mb4');
    }
    return $cn;
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function str_or_null(?string $s): ?string {
    $s = trim((string)$s);
    return $s === '' ? null : $s;
}

function int_or_null($v): ?int {
    if ($v === null) return null;
    $v = filter_var($v, FILTER_VALIDATE_INT);
    return $v === false ? null : $v;
}

function valid_email(?string $s): ?string {
    $s = str_or_null($s);
    if (!$s) return null;
    return filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : null;
}

function paginate(): array {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per = max(1, min(100, (int)($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $per;
    return [$page, $per, $offset];
}

function render_pagination(int $total, int $page, int $per, string $baseUrl): void {
    $pages = max(1, (int)ceil($total / $per));
    if ($pages <= 1) return;
    echo '<nav style="margin-top:10px">';
    for ($p = 1; $p <= $pages; $p++) {
        $active = $p === $page ? 'font-weight:bold' : '';
        $url = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . 'page=' . $p . '&per_page=' . $per;
        echo "<a style='margin-right:6px;$active' href='" . e($url) . "'>" . $p . "</a>";
    }
    echo '</nav>';
}
