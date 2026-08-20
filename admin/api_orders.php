<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$since = isset($_GET['since']) ? (int) $_GET['since'] : 0;

$total_menu = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM menu"))['c'];
$total_pesanan = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan"))['c'];
$pesanan_menunggu = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE status IN ('Menunggu','Menunggu Pembayaran')"))['c'];
$total_pendapatan = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga), 0) AS t FROM pesanan WHERE status='Selesai'"))['t'] ?? 0);

$pesanan_baru = [];
$q = mysqli_query($conn, "SELECT * FROM pesanan WHERE id > $since ORDER BY id DESC LIMIT 5");
while ($r = mysqli_fetch_assoc($q)) {
    $pesanan_baru[] = $r;
}

$latest_id = 0;
if (count($pesanan_baru) > 0) {
    $latest_id = (int) $pesanan_baru[0]['id'];
} else {
    $q2 = mysqli_query($conn, "SELECT MAX(id) AS max_id FROM pesanan");
    $r2 = mysqli_fetch_assoc($q2);
    $latest_id = (int) ($r2['max_id'] ?? 0);
}

echo json_encode([
    'card_total_menu' => $total_menu,
    'card_total_pesanan' => $total_pesanan,
    'card_pesanan_menunggu' => $pesanan_menunggu,
    'card_total_pendapatan' => $total_pendapatan,
    'latest_id' => $latest_id,
    'pesanan' => $pesanan_baru,
]);
