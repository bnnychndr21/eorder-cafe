<?php
session_start();
include 'config/koneksi.php';

if(empty($_SESSION['cart'])){
    header("Location: cart.php");
    exit;
}

$total = 0;

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qris_image VARCHAR(255) NOT NULL DEFAULT '',
    ewallet_number VARCHAR(100) NOT NULL DEFAULT '',
    cash_instruction TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$payment_settings = mysqli_query($conn, "SELECT * FROM payment_settings ORDER BY id DESC LIMIT 1");
$payment_settings = mysqli_fetch_assoc($payment_settings);

if(!$payment_settings){
    mysqli_query($conn, "INSERT INTO payment_settings (qris_image, ewallet_number, cash_instruction) VALUES ('', '', 'Silakan membayar di kasir dengan jumlah total harga.')");
    $payment_settings = mysqli_query($conn, "SELECT * FROM payment_settings ORDER BY id DESC LIMIT 1");
    $payment_settings = mysqli_fetch_assoc($payment_settings);
}

foreach($_SESSION['cart'] as $item){

    $menu_id = $item['menu_id'];

    $menu_id = (int) $item['menu_id'];

    $menu = mysqli_query(
        $conn,
        "SELECT * FROM menu WHERE id=$menu_id"
    );

    $row = mysqli_fetch_assoc($menu);

    $subtotal = $row['harga'] * $item['qty'];

    $total += $subtotal;
}

/*
|--------------------------------------------------------------------------
| SIMPAN PESANAN
|--------------------------------------------------------------------------
*/

if(isset($_POST['checkout'])){

    $nama_pelanggan = mysqli_real_escape_string(
        $conn,
        $_POST['nama_pelanggan']
    );

    $metode_pembayaran = mysqli_real_escape_string(
        $conn,
        $_POST['metode_pembayaran'] ?? 'QRIS'
    );

    $nomor_meja = isset($_POST['nomor_meja'])
        ? (int) $_POST['nomor_meja']
        : (int) ($_SESSION['meja'] ?? 0);

    if($nomor_meja <= 0){
        $nomor_meja = 0;
    }

    $status = ($metode_pembayaran === 'Cash') ? 'Menunggu' : 'Menunggu Pembayaran';

    mysqli_query(
        $conn,
        "INSERT INTO pesanan
        (nama_pelanggan,nomor_meja,total_harga,status,metode_pembayaran)
        VALUES
        (
        '$nama_pelanggan',
        $nomor_meja,
        $total,
        '$status',
        '$metode_pembayaran'
        )"
    );

    $pesanan_id = mysqli_insert_id($conn);

    foreach($_SESSION['cart'] as $item){

        $menu_id = (int) $item['menu_id'];

        $menu = mysqli_query(
            $conn,
            "SELECT * FROM menu WHERE id=$menu_id"
        );

        $row = mysqli_fetch_assoc($menu);

        $subtotal = $row['harga'] * $item['qty'];

        $qty = (int) $item['qty'];
        mysqli_query(
            $conn,
            "INSERT INTO detail_pesanan
            (
            pesanan_id,
            menu_id,
            qty,
            subtotal
            )
            VALUES
            (
            $pesanan_id,
            $menu_id,
            $qty,
            $subtotal
            )"
        );
    }

    unset($_SESSION['cart']);

    header("Location: sukses.php?id=" . $pesanan_id);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Checkout</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--radius:16px;--radius-sm:10px;}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f8f6f3;}
@keyframes fadeInUp{from{transform:translateY(50px);opacity:0}to{transform:translateY(0);opacity:1}}
.container{max-width:700px;margin:50px auto;}
.card{background:white;padding:30px 35px;border-radius:var(--radius);box-shadow:0 2px 16px rgba(0,0,0,.08);animation:fadeInUp .7s ease;border:1px solid rgba(0,0,0,.04);}
h1{text-align:center;margin-bottom:24px;color:var(--primary);font-size:24px;font-weight:600;}
label{display:block;margin-top:16px;margin-bottom:6px;color:#444;font-weight:500;font-size:14px;}
.payment-box{margin-top:14px;padding:16px 18px;border:1px solid #f0d6d6;border-radius:var(--radius-sm);background:#fff7f7;display:none;opacity:0;transform:translateY(10px);transition:opacity .3s ease, transform .3s ease;}
.payment-box.active{display:block;opacity:1;transform:translateY(0);}
.payment-box img{width:100%;max-width:260px;border-radius:var(--radius-sm);display:block;margin-top:10px;}
.payment-box strong{color:var(--primary-dark);}
.note{margin-top:10px;color:#888;font-size:13px;}
input,select{width:100%;padding:12px 16px;border:2px solid #eee;border-radius:var(--radius-sm);font-size:14px;outline:none;transition:border-color .3s;background:white;color:#333;}
input:focus,select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(163,22,33,.08);}
.total{margin-top:24px;font-size:28px;font-weight:700;color:var(--primary);text-align:right;padding-top:16px;border-top:2px dashed #eee;}
button{width:100%;margin-top:24px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;border:none;padding:15px;border-radius:var(--radius-sm);cursor:pointer;font-size:16px;font-weight:600;transition:all .3s;box-shadow:0 4px 15px rgba(163,22,33,.25);}
button:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(163,22,33,.35);}
button:active{transform:translateY(0);}
</style>

</head>

<body>

<div class="container">

<div class="card">

<h1>Checkout Pesanan</h1>

<form method="POST">

<label>Nama Pelanggan</label>

<input
type="text"
name="nama_pelanggan"
required>

<label>Nomor Meja</label>

<input
    type="number"
    name="nomor_meja"
    id="nomorMeja"
    min="1"
    placeholder="Masukkan nomor meja"
    value="<?php echo htmlspecialchars($_SESSION['meja'] ?? ''); ?>"
    required>

<label>Metode Pembayaran</label>

<select id="paymentMethod" name="metode_pembayaran" required>
    <option value="QRIS">QRIS</option>
    <option value="E-Wallet">E-Wallet (Dana/OVO/Gopay)</option>
    <option value="Cash">Cash / Bayar di Kasir</option>
</select>

<div id="qrisInfo" class="payment-box active">
    <strong>Silakan scan QRIS di bawah ini.</strong>
    <?php if(!empty($payment_settings['qris_image'])): ?>
        <img src="assets/img/payment/<?php echo basename($payment_settings['qris_image']); ?>" alt="QRIS Payment">
    <?php else: ?>
        <p>QRIS belum diupload oleh admin.</p>
    <?php endif; ?>
</div>

<div id="ewalletInfo" class="payment-box">
    <strong>Transfer ke nomor E-Wallet berikut:</strong>
    <p><?php echo htmlspecialchars($payment_settings['ewallet_number'] ?: 'Nomor e-wallet belum diatur admin.'); ?></p>
    <p>Silakan transfer ke nomor e-wallet ini untuk menyelesaikan pembayaran.</p>
</div>

<div id="cashInfo" class="payment-box">
    <strong>Pembayaran di kasir</strong>
    <p>Total pembayaran: Rp <?php echo number_format($total,0,',','.'); ?></p>
    <p><?php echo htmlspecialchars($payment_settings['cash_instruction'] ?: 'Silakan membayar di kasir dengan jumlah total harga.'); ?></p>
</div>

<p class="note">Pesanan akan masuk status Menunggu Pembayaran sampai pembayaran dikonfirmasi.</p>

<div class="total">

Total :
Rp <?php echo number_format($total,0,',','.'); ?>

</div>

<button type="submit" name="checkout">Buat Pesanan</button>

</form>

</div>

</div>

<script>
const paymentMethod = document.getElementById('paymentMethod');
const qrisInfo = document.getElementById('qrisInfo');
const ewalletInfo = document.getElementById('ewalletInfo');
const cashInfo = document.getElementById('cashInfo');

function updatePaymentInfo() {
    const value = paymentMethod.value;
    qrisInfo.classList.toggle('active', value === 'QRIS');
    ewalletInfo.classList.toggle('active', value === 'E-Wallet');
    cashInfo.classList.toggle('active', value === 'Cash');
}

paymentMethod.addEventListener('change', updatePaymentInfo);
updatePaymentInfo();
</script>

</body>
</html>