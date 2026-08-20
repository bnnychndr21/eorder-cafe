<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'admin';

$id = (int) $_GET['id'];

$pesanan = mysqli_query(
    $conn,
    "SELECT * FROM pesanan WHERE id=$id"
);

$data = mysqli_fetch_assoc($pesanan);
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Pesanan</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--radius:14px;--radius-sm:10px;}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
@keyframes fadeInUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
body{background:#f0eeeb;padding:30px;}
.card{background:white;padding:30px;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);max-width:900px;margin:auto;animation:fadeInUp .6s ease;border:1px solid rgba(0,0,0,.04);}
h1{color:var(--primary);font-size:24px;margin-bottom:20px;font-weight:600;}
.info{background:#faf8f6;padding:18px 20px;border-radius:var(--radius-sm);margin-bottom:20px;border:1px solid #f0eeeb;}
.info p{margin:6px 0;font-size:14px;color:#555;}
.info b{color:#2d2d2d;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th{background:var(--primary-dark);color:white;padding:12px 14px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;}
td{padding:12px 14px;border-bottom:1px solid #f0eeeb;color:#555;font-size:13px;}
.total{margin-top:20px;font-size:24px;font-weight:700;color:var(--primary);text-align:right;padding-top:16px;border-top:2px dashed #eee;}
.btn{display:inline-block;margin-top:20px;padding:10px 20px;text-decoration:none;border-radius:var(--radius-sm);border:none;cursor:pointer;font-size:14px;font-weight:500;transition:all .3s;color:white;}
.btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.15);}
.btn-back{background:#666;}
.btn-print{background:#1976d2;margin-left:10px;}
@media print{
body{background:white;padding:10px;}
.no-print{display:none!important;}
.card{box-shadow:none;border-radius:0;padding:10px;max-width:320px;margin:auto;border:none;}
.info{background:none;border:none;padding:8px 0;}
h1{font-size:18px;text-align:center;margin-bottom:5px;color:#000;}
.info p{font-size:12px;margin:3px 0;}
table th{background:#a31621!important;color:white!important;font-size:11px;padding:6px;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
table td{font-size:11px;padding:6px;}
.total{font-size:16px;text-align:right;border-top:1px dashed #333;}
.struk-footer{text-align:center;margin-top:15px;font-size:11px;border-top:1px dashed #333;padding-top:10px;}
.struk-header{text-align:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px dashed #333;}
.struk-header h2{font-size:16px;margin-bottom:2px;}
.struk-header p{font-size:10px;color:#666;}
}
</style>

</head>

<body>

<div class="card">

<div class="struk-header">
<h2>D'LAROZ</h2>
<p>D'Laroz Cafe</p>
</div>

<h1>Detail Pesanan #<?php echo $data['id']; ?></h1>

<div class="info">

<p>
<b>Nama Pelanggan :</b>
<?php echo $data['nama_pelanggan']; ?>
</p>

<p>
<b>Nomor Meja :</b>
<?php echo $data['nomor_meja']; ?>
</p>

<p>
<b>Status :</b>
<?php echo $data['status']; ?>
</p>

<p>
<b>Metode Pembayaran :</b>
<?php echo htmlspecialchars($data['metode_pembayaran'] ?? 'QRIS'); ?>
</p>

</div>

<table>

<tr>
<th>Menu</th>
<th>Qty</th>
<th>Subtotal</th>
</tr>

<?php

$detail = mysqli_query(
$conn,
"SELECT detail_pesanan.*,
menu.nama_menu
FROM detail_pesanan
JOIN menu
ON detail_pesanan.menu_id = menu.id
WHERE detail_pesanan.pesanan_id=$id"
);

while($row = mysqli_fetch_assoc($detail)){
?>

<tr>

<td>
<?php echo $row['nama_menu']; ?>
</td>

<td>
<?php echo $row['qty']; ?>
</td>

<td>
Rp <?php echo number_format($row['subtotal'],0,',','.'); ?>
</td>

</tr>

<?php
}
?>

</table>

<div class="total">

Total :
Rp <?php echo number_format($data['total_harga'],0,',','.'); ?>

</div>

<a href="pesanan.php" class="btn btn-back no-print">Kembali</a>
<button onclick="window.print()" class="btn btn-print no-print">Cetak Struk</button>

<div class="struk-footer">
<p>Terima kasih telah memesan di D'Laroz Cafe</p>
<p> Nikmati hidangan Anda!</p>
</div>

</div>

</body>
</html>