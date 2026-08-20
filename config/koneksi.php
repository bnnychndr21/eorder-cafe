<?php

$host = getenv('DB_HOST') ?: "127.0.0.1";
$port = getenv('DB_PORT') ?: "3306";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "";
$db   = getenv('DB_NAME') ?: "eorder_cafe";

$conn = mysqli_init();

if (getenv('DB_SSL') === 'true') {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
}

mysqli_real_connect($conn, $host, $user, $pass, $db, (int) $port);

if (mysqli_connect_errno()) {
    die("Koneksi gagal : " . mysqli_connect_error());
}
?>