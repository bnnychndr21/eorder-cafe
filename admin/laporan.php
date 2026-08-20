<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$bulan = isset($_GET['bulan']) ? (int) $_GET['bulan'] : (int) date('m');
$tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : (int) date('Y');

$start_date = "$tahun-$bulan-01";
$end_date = date('Y-m-t', strtotime($start_date));

$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan WHERE DATE(created_at) >= '$start_date' AND DATE(created_at) <= '$end_date'"))['total'] ?? 0;

$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga), 0) AS total FROM pesanan WHERE status='Selesai' AND DATE(created_at) >= '$start_date' AND DATE(created_at) <= '$end_date'"))['total'] ?? 0;

$pesanan = mysqli_query($conn, "SELECT * FROM pesanan WHERE DATE(created_at) >= '$start_date' AND DATE(created_at) <= '$end_date' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan</title>
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
        .header-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;}
        h1{color:#2d2d2d;font-size:24px;font-weight:600;}
        .filter-form{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .filter-form select,.filter-form button{padding:10px 16px;border-radius:var(--radius-sm);border:2px solid #eee;font-size:14px;outline:none;transition:border-color .3s;}
        .filter-form select:focus{border-color:var(--primary);}
        .filter-form button{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;border:none;cursor:pointer;transition:all .3s;font-weight:500;}
        .filter-form button:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(163,22,33,.3);}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:25px;}
        .card{background:white;padding:24px;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeInUp .5s ease backwards;transition:all .3s;border:1px solid rgba(0,0,0,.04);position:relative;overflow:hidden;}
        .card::after{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:linear-gradient(to bottom,var(--primary),var(--primary-light));border-radius:0 4px 4px 0;}
        .card:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,.1);}
        .card h3{color:#888;font-size:13px;font-weight:500;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;}
        .card h2{color:var(--primary);font-size:24px;font-weight:700;}
        table{width:100%;background:white;border-collapse:separate;border-spacing:0;border-radius:var(--radius);overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeInUp .6s ease;}
        th{background:var(--primary-dark);color:white;padding:14px;text-align:left;font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.5px;}
        td{padding:12px 14px;border-bottom:1px solid #f0eeeb;color:#555;font-size:13px;}
        tbody tr:hover td{background:#faf8f6;}
        .btn-print{display:inline-block;background:#1976d2;color:white;padding:10px 20px;border-radius:var(--radius-sm);text-decoration:none;border:none;cursor:pointer;font-size:14px;transition:all .3s;font-weight:500;}
        .btn-print:hover{background:#1565c0;transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.15);}
        .print-header{display:none;}
        .status{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;}
        .status-menunggu{background:#fff3e0;color:#e65100;}
        .status-diproses{background:#e3f2fd;color:#1565c0;}
        .status-selesai{background:#e8f5e9;color:#2e7d32;}
        @media print{
            body{background:white;}
            .sidebar,.filter-form,.btn-print,.no-print{display:none!important;}
            .content{margin-left:0;padding:20px;}
            .cards .card{box-shadow:none;border:1px solid #ddd;}
            .card::after{display:none;}
            table{box-shadow:none;border-radius:0;}
            th{background:#6d1b1b!important;color:white!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
            td,th{border:1px solid #000;padding:8px 10px;}
            .print-header{display:block;text-align:center;margin-bottom:20px;padding-bottom:10px;border-bottom:2px solid #6d1b1b;}
            .print-header h2{font-size:22px;color:#6d1b1b;margin-bottom:2px;}
            .print-header p{font-size:12px;color:#666;}
            h1{font-size:18px;}
        }
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
    <div class="print-header">
        <h2> D'LAROZ</h2>
        <p>D'Laroz Cafe - Laporan Bulanan</p>
    </div>

    <div class="header-row">
        <h1>Laporan Bulanan</h1>
        <div class="no-print">
            <button class="btn-print" onclick="window.print()"> Cetak Laporan</button>
        </div>
    </div>

    <form method="GET" class="filter-form no-print">
        <label>Bulan:</label>
        <select name="bulan">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo $m == $bulan ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>
        <label>Tahun:</label>
        <select name="tahun">
            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $y == $tahun ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>
        <button type="submit">Tampilkan</button>
    </form>

    <p style="margin-bottom:20px;color:#666;">
        Periode: <strong><?php echo date('F Y', mktime(0, 0, 0, $bulan, 1)); ?></strong>
    </p>

    <div class="cards">
        <div class="card">
            <h3>Total Pesanan</h3>
            <h2><?php echo $total_pesanan; ?></h2>
        </div>
        <div class="card">
            <h3>Total Pendapatan (Selesai)</h3>
            <h2>Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h2>
        </div>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Pelanggan</th>
            <th>Meja</th>
            <th>Total</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
        <?php if (mysqli_num_rows($pesanan) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($pesanan)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td><?php echo $row['nomor_meja']; ?></td>
                    <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['metode_pembayaran'] ?? 'QRIS'); ?></td>
                    <?php $sc = $row['status']=='Diproses'?'diproses':($row['status']=='Selesai'?'selesai':'menunggu'); ?>
                    <td><span class="status status-<?php echo $sc; ?>"><?php echo $row['status']; ?></span></td>
                    <td><?php echo isset($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">Belum ada data untuk periode ini.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
