<?php
session_start();
include 'config/koneksi.php';

/*
|--------------------------------------------------------------------------
| TAMBAH KE KERANJANG
|--------------------------------------------------------------------------
*/

if(isset($_POST['menu_id'])){

    $menu_id = $_POST['menu_id'];
    $qty = $_POST['qty'];

    if(!isset($_SESSION['cart'])){
        $_SESSION['cart'] = [];
    }

    $found = false;

    foreach($_SESSION['cart'] as $key => $item){

        if($item['menu_id'] == $menu_id){

            $_SESSION['cart'][$key]['qty'] += $qty;
            $found = true;
            break;
        }
    }

    if(!$found){

        $_SESSION['cart'][] = [
            'menu_id' => $menu_id,
            'qty' => $qty
        ];
    }

    header("Location: cart.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| HAPUS ITEM
|--------------------------------------------------------------------------
*/

if(isset($_GET['hapus'])){

    $hapus = $_GET['hapus'];

    unset($_SESSION['cart'][$hapus]);

    $_SESSION['cart'] = array_values($_SESSION['cart']);

    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Keranjang Belanja</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{--primary:#a31621;--primary-dark:#6d1b1b;--primary-light:#c0392b;--radius:16px;--radius-sm:10px;}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f8f6f3;}
@keyframes slideDown{from{transform:translateY(-80px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes fadeInRight{from{transform:translateX(60px);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes fadeInUp{from{transform:translateY(40px);opacity:0}to{transform:translateY(0);opacity:1}}
.header{background:linear-gradient(135deg,#4a0e0e,var(--primary),var(--primary-light));color:white;text-align:center;padding:30px 20px;animation:slideDown .7s ease;border-radius:0 0 24px 24px;box-shadow:0 4px 20px rgba(163,22,33,.25);}
.header h1{font-size:24px;font-weight:700;letter-spacing:.5px;}
.container{width:95%;max-width:1000px;margin:30px auto;}
.card{background:white;padding:20px 24px;border-radius:var(--radius);margin-bottom:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeInRight .5s ease backwards;border:1px solid rgba(0,0,0,.04);transition:transform .3s, box-shadow .3s;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;}
.card:hover{transform:translateX(4px);box-shadow:0 4px 20px rgba(0,0,0,.1);}
.card-left{flex:1;min-width:200px;}
.nama{font-size:18px;font-weight:600;color:#2d2d2d;}
.harga{color:var(--primary);font-weight:600;margin-top:4px;font-size:15px;}
.qty{margin-top:6px;color:#666;font-size:14px;}
.subtotal{font-weight:700;color:var(--primary-dark);font-size:16px;margin-top:6px;}
.card-right{text-align:right;}
.hapus{display:inline-block;background:#e53935;color:white;padding:7px 16px;text-decoration:none;border-radius:var(--radius-sm);font-size:13px;transition:all .3s;font-weight:500;}
.hapus:hover{background:#c62828;transform:translateY(-1px);box-shadow:0 4px 12px rgba(229,57,53,.3);}
.total-box{background:white;padding:24px 28px;border-radius:var(--radius);margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeInUp .6s ease;border:1px solid rgba(0,0,0,.04);}
.total{font-size:26px;color:var(--primary);font-weight:700;}
.btn{display:block;text-align:center;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;padding:15px;text-decoration:none;border-radius:var(--radius-sm);margin-top:18px;font-weight:600;font-size:16px;transition:all .3s;box-shadow:0 4px 15px rgba(163,22,33,.25);}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(163,22,33,.35);}
.kembali{display:inline-block;margin-top:16px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:500;transition:all .3s;}
.kembali:hover{color:var(--primary-dark);transform:translateX(-3px);}
.kosong{text-align:center;padding:60px 30px;background:white;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);}
.kosong h2{color:#666;margin-bottom:16px;font-weight:500;}
</style>

</head>

<body>

<div class="header">
    <h1>Keranjang Belanja</h1>
</div>

<div class="container">

<?php

$total = 0;

if(empty($_SESSION['cart'])){
?>

<div class="kosong">
<h2>Keranjang Masih Kosong</h2>
<a href="index.php" class="kembali">Kembali Belanja</a>
</div>

<?php

}else{

$i = 1;
foreach($_SESSION['cart'] as $key => $item){

$menu_id = (int) $item['menu_id'];

$data = mysqli_query(
$conn,
"SELECT * FROM menu WHERE id=$menu_id"
);

$row = mysqli_fetch_assoc($data);

$subtotal = $row['harga'] * $item['qty'];

$total += $subtotal;

?>

<div class="card" style="animation-delay:<?php echo $i++ * 0.1; ?>s">
<div class="card-left">
<div class="nama"><?php echo $row['nama_menu']; ?></div>
<div class="harga">Rp <?php echo number_format($row['harga'],0,',','.'); ?></div>
<div class="qty">Jumlah: <?php echo $item['qty']; ?></div>
<div class="subtotal">Rp <?php echo number_format($subtotal,0,',','.'); ?></div>
</div>
<div class="card-right">
<a href="cart.php?hapus=<?php echo $key; ?>" class="hapus" onclick="return confirm('Hapus menu ini?')">Hapus</a>
</div>
</div>

<?php
}
?>

<div class="total-box">
<div class="total">Rp <?php echo number_format($total,0,',','.'); ?></div>
<a href="checkout.php" class="btn">Checkout Sekarang</a>
<a href="index.php" class="kembali">+ Tambah Menu Lagi</a>
</div>

<?php
}
?>

</div>

</body>
</html>