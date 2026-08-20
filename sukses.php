<?php
session_start();
include 'config/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$data = mysqli_query($conn, "SELECT * FROM pesanan WHERE id=$id");
$pesanan = mysqli_fetch_assoc($data);

if(!$pesanan){
    header("Location: index.php");
    exit;
}

$payment_settings = mysqli_query($conn, "SELECT * FROM payment_settings ORDER BY id DESC LIMIT 1");
$payment_settings = mysqli_fetch_assoc($payment_settings);
?>
<!DOCTYPE html>
<html>
<head>
<title>Pesanan Berhasil</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--radius:16px;--radius-sm:10px;}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f8f6f3;padding:40px 20px;}
@keyframes scaleIn{from{transform:scale(.8);opacity:0}to{transform:scale(1);opacity:1}}
@keyframes fadeInUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.02)}}
.box{background:white;padding:40px;max-width:520px;margin:auto;border-radius:var(--radius);box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;animation:scaleIn .6s ease;border:1px solid rgba(0,0,0,.04);position:relative;}
.box::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--primary-dark),var(--primary),var(--primary-light));border-radius:var(--radius) var(--radius) 0 0;}
h1{color:#2e7d32;margin-bottom:6px;font-size:24px;font-weight:700;}
.sub-text{color:#888;font-size:14px;margin-bottom:16px;}
p{margin:6px 0;color:#555;font-size:14px;}
.info{background:#faf8f6;padding:18px 20px;border-radius:var(--radius-sm);margin:20px 0;text-align:left;animation:fadeInUp .5s ease;border:1px solid #f0eeeb;}
.info p{margin:4px 0;font-size:13px;}
.info b{color:#2d2d2d;}
.cash-notif{background:#fff8e1;color:#e65100;padding:18px 20px;border-radius:var(--radius-sm);margin:20px 0;font-size:15px;animation:pulse 2s infinite;border:1px solid #ffe082;font-weight:500;}
.cash-notif strong{display:block;margin-bottom:4px;font-size:16px;}
.payment-box{margin:20px 0;padding:20px 24px;border:1px solid #f0d6d6;border-radius:var(--radius-sm);background:#fff7f7;animation:fadeInUp .5s ease;}
.payment-box strong{color:var(--primary-dark);display:block;margin-bottom:8px;}
.payment-box img{width:100%;max-width:260px;border-radius:var(--radius-sm);display:block;margin:10px auto;}
.payment-box .nomor{font-size:24px;font-weight:700;color:var(--primary);margin-top:8px;letter-spacing:1px;}
.btn-kembali{display:inline-block;margin-top:20px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;text-decoration:none;padding:12px 28px;border-radius:var(--radius-sm);font-weight:500;transition:all .3s;box-shadow:0 4px 12px rgba(163,22,33,.25);}
.btn-kembali:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(163,22,33,.35);}
</style>
</head>
<body>

<div class="box">

<h1>Pesanan Berhasil</h1>
<p class="sub-text">Terima kasih telah memesan di D'Laroz Cafe.</p>

<div class="info">
<p><b>No. Pesanan:</b> #<?php echo $pesanan['id']; ?></p>
<p><b>Nama:</b> <?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></p>
<p><b>Meja:</b> <?php echo $pesanan['nomor_meja']; ?></p>
<p><b>Total:</b> Rp <?php echo number_format($pesanan['total_harga'],0,',','.'); ?></p>
<p><b>Status:</b> <?php echo $pesanan['status']; ?></p>
</div>

<?php if($pesanan['metode_pembayaran'] === 'Cash'): ?>
<div class="cash-notif">
<strong>Silakan bayar di kasir</strong>
Dengan nominal Rp <?php echo number_format($pesanan['total_harga'],0,',','.'); ?>
</div>

<?php elseif($pesanan['metode_pembayaran'] === 'QRIS'): ?>
<div class="payment-box">
<strong>Scan QRIS untuk membayar</strong>
<?php if(!empty($payment_settings['qris_image'])): ?>
<img src="assets/img/payment/<?php echo basename($payment_settings['qris_image']); ?>" alt="QRIS">
<?php else: ?>
<p>QRIS belum tersedia, silakan hubungi admin.</p>
<?php endif; ?>
</div>

<?php elseif($pesanan['metode_pembayaran'] === 'E-Wallet'): ?>
<div class="payment-box">
<strong>Transfer ke E-Wallet berikut</strong>
<p class="nomor"><?php echo htmlspecialchars($payment_settings['ewallet_number'] ?: 'Belum diatur'); ?></p>
</div>
<?php endif; ?>

<a href="index.php" class="btn-kembali">Kembali ke Menu</a>

</div>

</body>
</html>
