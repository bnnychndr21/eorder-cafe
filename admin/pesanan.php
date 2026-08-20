<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'admin';

/* UBAH STATUS */

if(isset($_GET['proses'])){

    $id = (int) $_GET['proses'];

    mysqli_query(
        $conn,
        "UPDATE pesanan
        SET status='Diproses'
        WHERE id=$id"
    );

    header("Location: pesanan.php");
    exit;
}

if(isset($_GET['selesai'])){

    $id = (int) $_GET['selesai'];

    mysqli_query(
        $conn,
        "UPDATE pesanan
        SET status='Selesai'
        WHERE id=$id"
    );

    header("Location: pesanan.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Kelola Pesanan</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--sidebar-bg:#1a1a2e;--radius:14px;--radius-sm:8px;}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
@keyframes fadeInUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes slideDown{from{transform:translateY(-60px);opacity:0}to{transform:translateY(0);opacity:1}}
body{background:#f0eeeb;}
.sidebar{position:fixed;width:250px;height:100%;background:var(--sidebar-bg);padding-top:0;animation:slideDown .6s ease;z-index:100;}
.logo{padding:25px 20px;text-align:center;color:white;font-size:22px;font-weight:700;letter-spacing:1px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:10px;}
.sidebar a{display:block;padding:13px 25px;color:rgba(255,255,255,.7);text-decoration:none;margin:2px 10px;border-radius:var(--radius-sm);transition:all .3s;font-size:14px;}
.sidebar a:hover{background:var(--primary);color:white;padding-left:30px;}
.content{margin-left:250px;padding:30px;animation:fadeInUp .7s ease;}
.content h1{font-size:24px;color:#2d2d2d;font-weight:600;}
table{width:100%;background:white;border-collapse:separate;border-spacing:0;border-radius:var(--radius);overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);}
th{background:var(--primary-dark);color:white;padding:14px;font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.5px;}
td{padding:12px 14px;border-bottom:1px solid #f0eeeb;color:#555;font-size:13px;}
tbody tr:hover td{background:#faf8f6;}
.btn{padding:6px 14px;border-radius:6px;text-decoration:none;color:white;font-size:12px;display:inline-block;margin:1px;transition:all .2s;font-weight:500;}
.btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.15);}
.proses{background:#f57c00;}
.selesai{background:#388e3c;}
.detail{background:#1976d2;}
.status{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;}
.status-menunggu{background:#fff3e0;color:#e65100;}
.status-diproses{background:#e3f2fd;color:#1565c0;}
.status-selesai{background:#e8f5e9;color:#2e7d32;}
</style>

</head>

<body>

<div class="sidebar">
<div class="logo">D'LAROZ</div>
<a href="dashboard.php">Dashboard</a>
<a href="pesanan.php">Pesanan</a>
<?php if($_SESSION['role'] === 'admin'): ?>
<a href="menu.php">Kelola Menu</a>
<a href="payment_settings.php">Pengaturan Pembayaran</a>
<a href="laporan.php">Laporan Bulanan</a>
<a href="qrcode_tables.php">QR Code Meja</a>
<?php endif; ?>
<a href="logout.php">Logout</a>
</div>

<div class="content">

<h1>Kelola Pesanan</h1>

<br>

<table>
<tr>
<th>ID</th><th>Nama</th><th>Meja</th><th>Total</th><th>Metode</th><th>Status</th><th>Aksi</th>
</tr>
<tbody id="ordersBody">
<?php
$data = mysqli_query($conn,"SELECT * FROM pesanan ORDER BY id DESC");
while($row = mysqli_fetch_assoc($data)):
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['nama_pelanggan']; ?></td>
<td>Meja <?php echo $row['nomor_meja']; ?></td>
<td>Rp <?php echo number_format($row['total_harga'],0,',','.'); ?></td>
<td><?php echo htmlspecialchars($row['metode_pembayaran'] ?? 'QRIS'); ?></td>
<?php
$sc = 'menunggu';
if($row['status']=='Diproses') $sc='diproses';
if($row['status']=='Selesai') $sc='selesai';
?>
<td><span class="status status-<?php echo $sc; ?>"><?php echo $row['status']; ?></span></td>
<td>
<a href="detail_pesanan.php?id=<?php echo $row['id']; ?>" class="btn detail">Detail</a>
<?php if($row['status']=="Menunggu" || $row['status']=="Menunggu Pembayaran"): ?>
<a href="?proses=<?php echo $row['id']; ?>" class="btn proses">Proses</a>
<?php endif; ?>
<?php if($row['status']=="Diproses"): ?>
<a href="?selesai=<?php echo $row['id']; ?>" class="btn selesai">Selesai</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<script>
async function refreshOrders() {
    try {
        const res = await fetch('api_orders_all.php');
        const orders = await res.json();
        const tbody = document.getElementById('ordersBody');
        let html = '';
        orders.forEach(r => {
            let aksi = '<a href="detail_pesanan.php?id=' + r.id + '" class="btn detail">Detail</a>';
            if (r.status === 'Menunggu' || r.status === 'Menunggu Pembayaran') {
                aksi += '<a href="?proses=' + r.id + '" class="btn proses">Proses</a>';
            }
            if (r.status === 'Diproses') {
                aksi += '<a href="?selesai=' + r.id + '" class="btn selesai">Selesai</a>';
            }
            html += '<tr><td>' + r.id + '</td><td>' + r.nama_pelanggan + '</td><td>Meja ' + r.nomor_meja + '</td><td>Rp ' + new Intl.NumberFormat('id-ID').format(r.total_harga) + '</td><td>' + (r.metode_pembayaran || 'QRIS') + '</td><td>' + r.status + '</td><td>' + aksi + '</td></tr>';
        });
        tbody.innerHTML = html;
    } catch (e) {}
}

setInterval(refreshOrders, 3000);
</script>

</div>

</body>
</html>