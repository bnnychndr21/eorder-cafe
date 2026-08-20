<?php
session_start();
include 'config/koneksi.php';

if(isset($_GET['meja'])){
    $_SESSION['meja'] = $_GET['meja'];
}

$meja = $_SESSION['meja'] ?? '';

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>D'Laroz Cafe</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#a31621;
    --primary-dark:#6d1b1b;
    --primary-light:#c0392b;
    --bg:#f8f6f3;
    --card-shadow:0 4px 20px rgba(0,0,0,.08);
    --card-shadow-hover:0 12px 40px rgba(163,22,33,.15);
    --radius:16px;
    --radius-sm:10px;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{background:var(--bg);}

@keyframes slideDown{from{transform:translateY(-100px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes fadeInUp{from{transform:translateY(40px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes fadeInLeft{from{transform:translateX(-40px);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}

.header{
    background:linear-gradient(135deg,#4a0e0e,#a31621,#c0392b);
    color:white;
    text-align:center;
    padding:45px 20px 35px;
    border-radius:0 0 30px 30px;
    position:relative;
    animation:slideDown .8s ease;
    box-shadow:0 4px 20px rgba(163,22,33,.3);
}

.header h1{
    font-size:28px;
    font-weight:700;
    letter-spacing:1px;
    margin-bottom:4px;
}

.header p{
    font-size:14px;
    opacity:.9;
}

.table-info{
    display:inline-block;
    margin-top:12px;
    background:rgba(255,255,255,.15);
    backdrop-filter:blur(10px);
    padding:6px 18px;
    border-radius:20px;
    font-size:13px;
}

.cart-btn{
    position:absolute;
    top:20px;
    right:20px;
    background:white;
    color:var(--primary);
    padding:10px 18px;
    border-radius:50px;
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    box-shadow:0 4px 12px rgba(0,0,0,.15);
    transition:transform .3s, box-shadow .3s;
}
.cart-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.2);}

.container{width:95%;max-width:1200px;margin:30px auto;}

.judul-kategori{
    font-size:24px;
    font-weight:600;
    color:var(--primary-dark);
    margin:35px 0 18px;
    padding-left:14px;
    position:relative;
}
.judul-kategori::before{
    content:'';
    position:absolute;
    left:0;
    top:4px;
    bottom:4px;
    width:4px;
    background:linear-gradient(to bottom,var(--primary),var(--primary-light));
    border-radius:4px;
}

.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;}

.card{
    background:white;
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow:var(--card-shadow);
    transition:transform .4s ease, box-shadow .4s ease;
    animation:fadeInUp .5s ease backwards;
}
.card:hover{
    transform:translateY(-8px);
    box-shadow:var(--card-shadow-hover);
}

.card img{width:100%;height:200px;object-fit:cover;}

.card-body{padding:18px 20px 20px;}

.nama{
    font-size:18px;
    font-weight:600;
    color:#2d2d2d;
}

.harga{
    color:var(--primary);
    font-size:20px;
    font-weight:700;
    margin-top:8px;
}

.qty{
    width:100%;
    padding:10px 14px;
    margin-top:12px;
    border:2px solid #eee;
    border-radius:var(--radius-sm);
    font-size:14px;
    transition:border-color .3s;
    outline:none;
}
.qty:focus{border-color:var(--primary);}

.btn{
    width:100%;
    margin-top:14px;
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    color:white;
    border:none;
    padding:12px;
    border-radius:var(--radius-sm);
    cursor:pointer;
    font-size:14px;
    font-weight:500;
    transition:transform .3s, box-shadow .3s;
    box-shadow:0 4px 12px rgba(163,22,33,.25);
}
.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 6px 20px rgba(163,22,33,.35);
}
.btn:active{transform:translateY(0);}

.cafe-info{
    max-width:1200px;
    width:95%;
    margin:-20px auto 30px;
    background:white;
    border-radius:var(--radius);
    box-shadow:0 8px 30px rgba(0,0,0,.08);
    padding:24px 30px;
    position:relative;
    z-index:2;
    animation:fadeInUp .6s ease backwards;
}

.cafe-info-inner{
    display:grid;
    grid-template-columns:1fr 1px 1fr;
    gap:0;
    align-items:start;
}

.cafe-info-divider{
    width:1px;
    height:100%;
    min-height:70px;
    background:linear-gradient(to bottom,transparent,#e0e0e0,transparent);
}

.cafe-info-item{
    padding:0 20px;
}

.cafe-info-item:first-child{
    padding-left:0;
}

.cafe-info-item:last-child{
    padding-right:0;
}

.cafe-info-item h3{
    font-size:13px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:1px;
    color:var(--primary);
    margin-bottom:6px;
}

.cafe-info-item p{
    font-size:14px;
    color:#555;
    line-height:1.7;
}

.cafe-info-item .label-icon{
    margin-right:6px;
}

.hours-list{
    list-style:none;
    font-size:14px;
    color:#555;
}

.hours-list li{
    display:flex;
    align-items:center;
    line-height:1.9;
}

.hours-list .day{
    font-weight:500;
    color:#333;
    min-width:105px;
}

.hours-list .time{
    color:#555;
}

.hours-list .closed{
    color:var(--primary);
    font-weight:500;
}

.footer{
    text-align:center;
    padding:30px 20px;
    color:#999;
    font-size:13px;
    border-top:1px solid #eee;
    margin-top:20px;
}

@media(max-width:640px){
    .cafe-info-inner{
        grid-template-columns:1fr;
        gap:16px;
    }
    .cafe-info-divider{
        display:none;
    }
    .cafe-info-item{
        padding:0;
    }
    .cafe-info-item:first-child{
        padding-bottom:12px;
        border-bottom:1px solid #f0f0f0;
    }
}

</style>
</head>
<body>

<div class="header">

<a href="cart.php" class="cart-btn">
Keranjang (<?= count($_SESSION['cart']); ?>)
</a>

<h1>D'LAROZ CAFE</h1>
<p>Pesan Menu Favoritmu Dengan Mudah</p>
<?php if($meja): ?>
<div class="table-info">Meja <?php echo $meja; ?></div>
<?php endif; ?>
</div>

<div class="cafe-info">
    <div class="cafe-info-inner">
        <div class="cafe-info-item">
            <h3><span class="label-icon">&#x1F4CD;</span> Lokasi</h3>
            <p>D'Laroz Cafe<br>Lorong Putro Raftah No.96, Kelurahan Paal Lima<br>Kota Jambi</p>
        </div>
        <div class="cafe-info-divider"></div>
        <div class="cafe-info-item">
            <h3><span class="label-icon">&#x1F552;</span> Jam Operasional</h3>
            <ul class="hours-list">
                <li><span class="day">Selasa - Sabtu</span><span class="time">14.30 - 22.00 WIB</span></li>
                <li><span class="day">Minggu</span><span class="time">16.00 - 23.00 WIB</span></li>
                <li><span class="day">Senin</span><span class="time closed">Tutup</span></li>
            </ul>
        </div>
    </div>
</div>

<div class="container">

<?php

$kategori = mysqli_query($conn,"SELECT DISTINCT kategori FROM menu");

while($kat = mysqli_fetch_assoc($kategori)){

echo "<h2 class='judul-kategori' style='animation:fadeInLeft .6s ease'>".$kat['kategori']."</h2>";
echo "<div class='grid'>";

$menu = mysqli_query($conn,"SELECT * FROM menu WHERE kategori='".$kat['kategori']."'");
$menu_idx = 0;

while($row = mysqli_fetch_assoc($menu)){
?>

<?php
$delay = $menu_idx ?? 0;
$menu_idx = ($menu_idx ?? 0) + 1;
?>
<div class="card" style="animation-delay:<?php echo $delay * 0.1; ?>s">

<?php if(!empty($row['gambar'])){ ?>
<img src="assets/img/<?php echo $row['gambar']; ?>">
<?php }else{ ?>
<img src="https://via.placeholder.com/500x300?text=D%27Laroz+Cafe">
<?php } ?>

<div class="card-body">

<div class="nama">
<?php echo $row['nama_menu']; ?>
</div>

<div class="harga">
Rp <?php echo number_format($row['harga'],0,',','.'); ?>
</div>

<form action="cart.php" method="POST">

<input type="hidden"
name="menu_id"
value="<?php echo $row['id']; ?>">

<input type="number"
name="qty"
value="1"
min="1"
class="qty">

<button type="submit" class="btn">
Tambah Ke Keranjang
</button>

</form>

</div>
</div>

<?php
}
echo "</div>";
}
?>

</div>

<div class="footer">
    &copy; 2026 D'Laroz Cafe
</div>

</body>
</html>