<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$data = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC");
$orders = [];
while ($r = mysqli_fetch_assoc($data)) {
    $orders[] = $r;
}

echo json_encode($orders);
