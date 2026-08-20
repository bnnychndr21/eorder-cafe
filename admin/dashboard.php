<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'admin';

if(isset($_GET['proses'])){
    $id = (int) $_GET['proses'];
    mysqli_query($conn,"UPDATE pesanan SET status='Diproses' WHERE id=$id");
    header("Location: dashboard.php");
    exit;
}

if(isset($_GET['selesai'])){
    $id = (int) $_GET['selesai'];
    mysqli_query($conn,"UPDATE pesanan SET status='Selesai' WHERE id=$id");
    header("Location: dashboard.php");
    exit;
}

$total_menu = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM menu"));
$total_pesanan = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pesanan"));
$pesanan_menunggu = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pesanan WHERE status IN ('Menunggu','Menunggu Pembayaran')"));
$data_pendapatan = mysqli_query($conn,"SELECT SUM(total_harga) AS total FROM pesanan");
$pendapatan = mysqli_fetch_assoc($data_pendapatan);
$total_pendapatan = $pendapatan['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $_SESSION['role'] === 'admin' ? 'Dashboard Admin' : 'Dashboard Kasir'; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--sidebar-bg:#1a1a2e;--radius:14px;--radius-sm:8px;}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f0eeeb;}
@keyframes fadeInUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes slideDown{from{transform:translateY(-60px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes countUp{from{opacity:0;transform:scale(.5)}to{opacity:1;transform:scale(1)}}
.sidebar{position:fixed;left:0;top:0;width:250px;height:100%;background:var(--sidebar-bg);padding-top:0;animation:slideDown .6s ease;z-index:100;}
.logo{padding:25px 20px;text-align:center;color:white;font-size:22px;font-weight:700;letter-spacing:1px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:10px;}
.sidebar a{display:block;color:rgba(255,255,255,.7);text-decoration:none;padding:13px 25px;margin:2px 10px;border-radius:var(--radius-sm);transition:all .3s;font-size:14px;}
.sidebar a:hover{background:var(--primary);color:white;padding-left:30px;}
.content{margin-left:250px;padding:30px;animation:fadeInUp .7s ease;}
.title{font-size:26px;color:#2d2d2d;margin-bottom:25px;font-weight:600;}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-bottom:30px;}
.card{background:white;padding:24px;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeInUp .5s ease backwards;transition:all .3s;border:1px solid rgba(0,0,0,.04);position:relative;overflow:hidden;}
.card::after{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:linear-gradient(to bottom,var(--primary),var(--primary-light));border-radius:0 4px 4px 0;}
.card:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,.1);}
.card h3{color:#888;font-size:13px;font-weight:500;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;}
.card h2{color:var(--primary);font-size:28px;font-weight:700;}
.recent{background:white;padding:24px;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeInUp .6s ease;border:1px solid rgba(0,0,0,.04);}
.recent h2{font-size:18px;color:#2d2d2d;margin-bottom:16px;}
table{width:100%;border-collapse:separate;border-spacing:0;}
table th,table td{padding:12px 14px;text-align:left;font-size:13px;}
table th{background:var(--primary-dark);color:white;font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.5px;}
table th:first-child{border-radius:8px 0 0 0;}
table th:last-child{border-radius:0 8px 0 0;}
table td{border-bottom:1px solid #f0eeeb;color:#555;}
table tbody tr:hover td{background:#faf8f6;}
.status{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;}
.status-menunggu{background:#fff3e0;color:#e65100;}
.status-diproses{background:#e3f2fd;color:#1565c0;}
.status-selesai{background:#e8f5e9;color:#2e7d32;}
.btn{padding:6px 14px;border-radius:6px;text-decoration:none;color:white;font-size:12px;display:inline-block;margin:1px;transition:all .2s;font-weight:500;border:none;cursor:pointer;}
.btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.15);}
.detail{background:#1976d2;}
.proses{background:#f57c00;}
.selesai{background:#388e3c;}
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
<a href="../index.php">Lihat Menu</a>
<?php endif; ?>
<a href="logout.php">Logout</a>
</div>

<div class="content">
<h1 class="title"><?php echo $_SESSION['role'] === 'admin' ? 'Dashboard Admin' : 'Dashboard Kasir'; ?></h1>

<div class="cards">
<div class="card"><h3>Total Menu</h3><h2 id="cardTotalMenu"><?php echo $total_menu; ?></h2></div>
<div class="card"><h3>Total Pesanan</h3><h2 id="cardTotalPesanan"><?php echo $total_pesanan; ?></h2></div>
<div class="card"><h3>Pesanan Menunggu</h3><h2 id="cardMenunggu"><?php echo $pesanan_menunggu; ?></h2></div>
<div class="card"><h3>Total Pendapatan</h3><h2 id="cardPendapatan">Rp <?php echo number_format($total_pendapatan,0,',','.'); ?></h2></div>
</div>

<div class="recent">
<h2>Pesanan Terbaru</h2><br>
<table>
<tr><th>ID</th><th>Nama</th><th>Meja</th><th>Total</th><th>Metode</th><th>Status</th><th>Aksi</th></tr>
<tbody id="recentOrders">
<?php
$pesanan = mysqli_query($conn,"SELECT * FROM pesanan ORDER BY id DESC LIMIT 5");
while($row = mysqli_fetch_assoc($pesanan)):
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['nama_pelanggan']; ?></td>
<td><?php echo $row['nomor_meja']; ?></td>
<td>Rp <?php echo number_format($row['total_harga'],0,',','.'); ?></td>
<td><?php echo htmlspecialchars($row['metode_pembayaran'] ?? 'QRIS'); ?></td>
<?php
$status_class = 'menunggu';
if($row['status']=='Diproses') $status_class='diproses';
if($row['status']=='Selesai') $status_class='selesai';
?>
<td><span class="status status-<?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
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
</div>
</div>

<script>
let latestId = <?php echo (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(id) AS max_id FROM pesanan"))['max_id'] ?? 0); ?>;

async function pollOrders() {
    try {
        const res = await fetch('api_orders.php?since=' + latestId);
        const data = await res.json();

        document.getElementById('cardTotalMenu').textContent = data.card_total_menu;
        document.getElementById('cardTotalPesanan').textContent = data.card_total_pesanan;
        document.getElementById('cardMenunggu').textContent = data.card_pesanan_menunggu;
        document.getElementById('cardPendapatan').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.card_total_pendapatan);

        if (data.pesanan.length > 0) {
            latestId = data.latest_id;
            const tbody = document.getElementById('recentOrders');
            let html = '';
            data.pesanan.forEach(r => {
                let aksi = '<a href="detail_pesanan.php?id=' + r.id + '" class="btn detail">Detail</a>';
                if (r.status === 'Menunggu' || r.status === 'Menunggu Pembayaran') {
                    aksi += '<a href="?proses=' + r.id + '" class="btn proses">Proses</a>';
                }
                if (r.status === 'Diproses') {
                    aksi += '<a href="?selesai=' + r.id + '" class="btn selesai">Selesai</a>';
                }
                let sc = 'menunggu';
                if(r.status === 'Diproses') sc = 'diproses';
                if(r.status === 'Selesai') sc = 'selesai';
                html += '<tr><td>' + r.id + '</td><td>' + r.nama_pelanggan + '</td><td>' + r.nomor_meja + '</td><td>Rp ' + new Intl.NumberFormat('id-ID').format(r.total_harga) + '</td><td>' + (r.metode_pembayaran || 'QRIS') + '</td><td><span class="status status-' + sc + '">' + r.status + '</span></td><td>' + aksi + '</td></tr>';
            });
            tbody.innerHTML = html;
        }
    } catch (e) {}
}

setInterval(pollOrders, 3000);
</script>

</body>
</html>
