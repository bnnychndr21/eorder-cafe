<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qris_image VARCHAR(255) NOT NULL DEFAULT '',
    ewallet_number VARCHAR(100) NOT NULL DEFAULT '',
    cash_instruction TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$settings = mysqli_query($conn, "SELECT * FROM payment_settings ORDER BY id DESC LIMIT 1");
$settings = mysqli_fetch_assoc($settings);

if(!$settings){
    mysqli_query($conn, "INSERT INTO payment_settings (qris_image, ewallet_number, cash_instruction) VALUES ('', '', 'Silakan membayar di kasir dengan jumlah total harga.')");
    $settings = mysqli_query($conn, "SELECT * FROM payment_settings ORDER BY id DESC LIMIT 1");
    $settings = mysqli_fetch_assoc($settings);
}

$message = '';

if(isset($_POST['save_payment'])){
    $ewallet_number = mysqli_real_escape_string($conn, $_POST['ewallet_number']);
    $cash_instruction = mysqli_real_escape_string($conn, $_POST['cash_instruction']);

    $qris_image = $settings['qris_image'];

    if(isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] == 0){
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['qris_image']['name'], PATHINFO_EXTENSION));

        if(!in_array($ext, $allowed)){
            $message = 'Format file QRIS harus JPG, JPEG, atau PNG.';
        } else {
            $file_name = 'qris_' . time() . '.' . $ext;
            $target = '../assets/img/payment/' . $file_name;

            if(move_uploaded_file($_FILES['qris_image']['tmp_name'], $target)){
                if(!empty($settings['qris_image']) && file_exists('../' . $settings['qris_image'])){
                    unlink('../' . $settings['qris_image']);
                }
                $qris_image = 'assets/img/payment/' . $file_name;
            }
        }
    }

    mysqli_query($conn, "UPDATE payment_settings SET qris_image='$qris_image', ewallet_number='$ewallet_number', cash_instruction='$cash_instruction' WHERE id='{$settings['id']}'");

    $settings['qris_image'] = $qris_image;
    $settings['ewallet_number'] = $ewallet_number;
    $settings['cash_instruction'] = $cash_instruction;

    $message = $message ?: 'Pengaturan pembayaran berhasil disimpan.';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Pembayaran</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--sidebar-bg:#1a1a2e;--radius:14px;--radius-sm:8px;}
*{box-sizing:border-box;font-family:'Poppins',sans-serif;margin:0;padding:0;}
@keyframes fadeInUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes slideDown{from{transform:translateY(-60px);opacity:0}to{transform:translateY(0);opacity:1}}
body{background:#f0eeeb;color:#333;}
.sidebar{position:fixed;left:0;top:0;width:250px;height:100%;background:var(--sidebar-bg);padding-top:0;animation:slideDown .6s ease;z-index:100;}
.logo{padding:25px 20px;text-align:center;color:white;font-size:22px;font-weight:700;letter-spacing:1px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:10px;}
.sidebar a{display:block;color:rgba(255,255,255,.7);text-decoration:none;padding:13px 25px;margin:2px 10px;border-radius:var(--radius-sm);transition:all .3s;font-size:14px;}
.sidebar a:hover{background:var(--primary);color:white;padding-left:30px;}
.content{margin-left:250px;padding:30px;animation:fadeInUp .7s ease;}
.card{background:white;padding:28px;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);max-width:900px;animation:fadeInUp .6s ease;border:1px solid rgba(0,0,0,.04);}
h1{color:var(--primary);font-size:22px;margin-bottom:6px;font-weight:600;}
.subtitle{color:#888;font-size:13px;margin-bottom:20px;}
label{display:block;margin-top:16px;margin-bottom:6px;font-weight:600;color:#444;font-size:14px;}
input, textarea{width:100%;padding:12px 16px;border:2px solid #eee;border-radius:var(--radius-sm);font-size:14px;outline:none;transition:border-color .3s;}
input:focus, textarea:focus{border-color:var(--primary);}
textarea{min-height:90px;resize:vertical;}
button{margin-top:20px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;border:none;padding:12px 24px;border-radius:var(--radius-sm);cursor:pointer;font-size:15px;font-weight:500;transition:all .3s;box-shadow:0 4px 12px rgba(163,22,33,.25);}
button:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(163,22,33,.35);}
.msg{margin-top:15px;padding:12px 16px;border-radius:var(--radius-sm);background:#e8f5e9;color:#2e7d32;font-size:13px;border:1px solid #c8e6c9;}
.msg.error{background:#fff5f5;color:#c0392b;border-color:#fdd;}
.preview{margin-top:10px;max-width:220px;border:2px solid #eee;border-radius:var(--radius-sm);padding:8px;background:#fafafa;}
.preview img{width:100%;border-radius:6px;display:block;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="logo">D'LAROZ</div>
  <a href="dashboard.php">Dashboard</a>
  <a href="menu.php">Kelola Menu</a>
  <a href="pesanan.php">Pesanan</a>
  <a href="payment_settings.php">Pengaturan Pembayaran</a>
  <a href="laporan.php">Laporan Bulanan</a>
  <a href="qrcode_tables.php">QR Code Meja</a>
  <a href="logout.php">Logout</a>
</div>
<div class="content">
  <div class="card">
    <h1>Pengaturan Pembayaran</h1>
    <p class="subtitle">Upload gambar QRIS, atur nomor e-wallet, dan isi instruksi pembayaran cash.</p>

    <?php if($message): ?>
      <div class="msg <?php echo strpos($message, 'berhasil') !== false ? '' : 'error'; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label>Upload Gambar QRIS</label>
      <input type="file" name="qris_image" accept="image/png,image/jpg,image/jpeg">
      <?php if(!empty($settings['qris_image'])): ?>
        <div class="preview">
          <img src="../<?php echo $settings['qris_image']; ?>" alt="QRIS Preview">
        </div>
      <?php endif; ?>

      <label>Nomor E-Wallet</label>
      <input type="text" name="ewallet_number" value="<?php echo htmlspecialchars($settings['ewallet_number']); ?>" placeholder="Contoh: 0812xxxxxx">

      <label>Instruksi Pembayaran Cash</label>
      <textarea name="cash_instruction" placeholder="Silakan membayar di kasir dengan jumlah total harga."><?php echo htmlspecialchars($settings['cash_instruction']); ?></textarea>

      <button type="submit" name="save_payment">Simpan Pengaturan</button>
    </form>
  </div>
</div>
</body>
</html>
