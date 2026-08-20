<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/meja/(\d+)$#', $uri, $m)) {
    $_GET['meja'] = $m[1];
    $_REQUEST['meja'] = $m[1];
    include __DIR__ . '/index.php';
    return true;
}

if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

return false;
