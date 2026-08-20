<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    ob_start();
    include base_path('index.php');
    return response(ob_get_clean(), 200)->header('Content-Type', 'text/html');
});

Route::get('/meja/{meja}', function (string $meja) {
    $_GET['meja'] = $meja;
    $_REQUEST['meja'] = $meja;

    ob_start();
    include base_path('index.php');
    return response(ob_get_clean(), 200)->header('Content-Type', 'text/html');
});

Route::get('/admin/login.php', function () {
    ob_start();
    include base_path('admin/login.php');
    return response(ob_get_clean(), 200)->header('Content-Type', 'text/html');
});

Route::post('/admin/login.php', function () {
    ob_start();
    include base_path('admin/login.php');
    return response(ob_get_clean(), 200)->header('Content-Type', 'text/html');
});

Route::get('/admin/dashboard.php', function () {
    ob_start();
    include base_path('admin/dashboard.php');
    return response(ob_get_clean(), 200)->header('Content-Type', 'text/html');
});

Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'], '/{any?}', function (Request $request) {
    $path = $request->path();
    $candidate = base_path($path);

    if ($path !== '/' && is_file($candidate)) {
        $request->setLaravelSession($request->session());
        ob_start();
        include $candidate;
        return response(ob_get_clean(), 200)->header('Content-Type', 'text/html');
    }

    return response('Not Found', 404);
})->where('any', '.*');
