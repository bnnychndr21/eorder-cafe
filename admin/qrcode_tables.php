<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$env_path = dirname(__DIR__) . '/.env';
$base_url = 'https://livestock-crept-selection.ngrok-free.dev';

if (file_exists($env_path)) {
    $env_content = file_get_contents($env_path);
    if (preg_match('/^APP_URL=(.+)$/m', $env_content, $matches)) {
        $base_url = trim($matches[1]);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>QR Code Meja</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--sidebar-bg:#1a1a2e;--radius:14px;--radius-sm:8px;}
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        @keyframes fadeInUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
        @keyframes slideDown{from{transform:translateY(-60px);opacity:0}to{transform:translateY(0);opacity:1}}
        body{background:#f0eeeb;}
        .sidebar{position:fixed;left:0;top:0;width:250px;height:100%;background:var(--sidebar-bg);padding-top:0;animation:slideDown .6s ease;z-index:100;}
        .logo{padding:25px 20px;text-align:center;color:white;font-size:22px;font-weight:700;letter-spacing:1px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:10px;}
        .sidebar a{display:block;color:rgba(255,255,255,.7);text-decoration:none;padding:13px 25px;margin:2px 10px;border-radius:var(--radius-sm);transition:all .3s;font-size:14px;}
        .sidebar a:hover{background:var(--primary);color:white;padding-left:30px;}
        .content{margin-left:250px;padding:30px;animation:fadeInUp .7s ease;}
        h1{color:#2d2d2d;font-size:24px;font-weight:600;margin-bottom:25px;}
        .info{background:#fff8e1;border:1px solid #ffe082;padding:16px 20px;border-radius:var(--radius-sm);margin-bottom:25px;color:#e65100;font-size:14px;}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;}
        .card{background:white;border-radius:var(--radius);padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center;animation:fadeInUp .5s ease backwards;transition:all .3s;border:1px solid rgba(0,0,0,.04);}
        .card:hover{transform:translateY(-5px);box-shadow:0 12px 30px rgba(0,0,0,.1);}
        .card img{width:100%;max-width:280px;border-radius:var(--radius-sm);}
        .card h2{margin:15px 0 8px;color:var(--primary-dark);font-size:20px;}
        .card p{color:#888;font-size:13px;word-break:break-all;margin-bottom:15px;}
        .btn-download{display:inline-block;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;padding:10px 24px;border-radius:var(--radius-sm);text-decoration:none;font-size:14px;font-weight:500;transition:all .3s;box-shadow:0 4px 12px rgba(163,22,33,.25);}
        .btn-download:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(163,22,33,.35);}
        @media print{.sidebar,.info,.btn-download{display:none;}.content{margin-left:0;}}
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
    <a href="../index.php">Lihat Menu</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h1> QR Code Meja</h1>
    <p style="margin-bottom:25px;color:#666;">Scan QR code ini untuk langsung membuka menu sesuai nomor meja.</p>

    <div class="grid">
        <?php
        $result = mysqli_query($conn, "SELECT id, nomor_meja, qr_code FROM meja ORDER BY nomor_meja");
        while ($row = mysqli_fetch_assoc($result)):
            $nomor_meja = (int) $row['nomor_meja'];
            $qr_url = $row['qr_code'];
            if (empty($qr_url)) {
                $qr_url = $base_url . '/meja' . $nomor_meja . '.php';
            }
            $api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qr_url);
        ?>
            <div class="card">
                <img src="<?php echo $api_url; ?>" alt="Meja <?php echo $nomor_meja; ?>">
                <h2>Meja <?php echo $nomor_meja; ?></h2>
                <p><?php echo htmlspecialchars($qr_url); ?></p>
                <a class="btn-download" href="<?php echo $api_url; ?>" download="qrcode_meja_<?php echo $nomor_meja; ?>.png">
                    ⬇ Download
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>