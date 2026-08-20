<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

if(isset($_GET['hapus'])){
    $id = (int) $_GET['hapus'];
    $q = mysqli_query($conn, "SELECT gambar FROM menu WHERE id=$id");
    $menu = mysqli_fetch_assoc($q);
    if($menu && !empty($menu['gambar'])){
        $file_path = '../assets/img/' . $menu['gambar'];
        if(file_exists($file_path)){
            unlink($file_path);
        }
    }
    mysqli_query($conn, "DELETE FROM menu WHERE id=$id");
    header("Location: menu.php");
    exit;
}

if(isset($_POST['simpan'])){
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = (int) $_POST['harga'];
    $edit_id = isset($_POST['edit_id']) ? (int) $_POST['edit_id'] : 0;
    $gambar = '';

    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0){
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)){
            $message = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';
            $message_type = 'error';
        } else {
            $file_name = str_replace(' ', '_', strtolower($nama_menu)) . '_' . time() . '.' . $ext;
            $target = '../assets/img/' . $file_name;
            if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target)){
                $gambar = $file_name;
                if($edit_id > 0){
                    $q = mysqli_query($conn, "SELECT gambar FROM menu WHERE id=$edit_id");
                    $old = mysqli_fetch_assoc($q);
                    if($old && !empty($old['gambar']) && $old['gambar'] !== $gambar){
                        $old_path = '../assets/img/' . $old['gambar'];
                        if(file_exists($old_path)){
                            unlink($old_path);
                        }
                    }
                }
            }
        }
    }

    if(!$message){
        if($edit_id > 0){
            if($gambar){
                mysqli_query($conn, "UPDATE menu SET nama_menu='$nama_menu', kategori='$kategori', harga=$harga, gambar='$gambar' WHERE id=$edit_id");
            } else {
                mysqli_query($conn, "UPDATE menu SET nama_menu='$nama_menu', kategori='$kategori', harga=$harga WHERE id=$edit_id");
            }
            $message = 'Menu berhasil diperbarui.';
        } else {
            if(!$gambar){
                mysqli_query($conn, "INSERT INTO menu (nama_menu, kategori, harga) VALUES ('$nama_menu', '$kategori', $harga)");
            } else {
                mysqli_query($conn, "INSERT INTO menu (nama_menu, kategori, harga, gambar) VALUES ('$nama_menu', '$kategori', $harga, '$gambar')");
            }
            $message = 'Menu berhasil ditambahkan.';
        }
        $message_type = 'success';
    }
}

$edit_data = null;
if(isset($_GET['edit'])){
    $edit_id = (int) $_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM menu WHERE id=$edit_id");
    $edit_data = mysqli_fetch_assoc($q);
}

$menu_items = mysqli_query($conn, "SELECT * FROM menu ORDER BY kategori, nama_menu");

$kategoris = mysqli_query($conn, "SELECT DISTINCT kategori FROM menu ORDER BY kategori");
$all_kategoris = [];
while($k = mysqli_fetch_assoc($kategoris)){
    $all_kategoris[] = $k['kategori'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Menu</title>
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
.sidebar a:hover,.sidebar a.active{background:var(--primary);color:white;padding-left:30px;}
.content{margin-left:250px;padding:30px;animation:fadeInUp .7s ease;}
.content h1{font-size:24px;color:#2d2d2d;font-weight:600;}
.layout{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;}
@media(max-width:1000px){.layout{grid-template-columns:1fr;}}
.card{background:white;padding:24px;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.04);animation:fadeInUp .5s ease;}
.card h2{font-size:17px;color:var(--primary);font-weight:600;margin-bottom:16px;}
label{display:block;margin-top:14px;margin-bottom:5px;font-weight:600;color:#444;font-size:13px;}
input,select{width:100%;padding:10px 14px;border:2px solid #eee;border-radius:var(--radius-sm);font-size:13px;outline:none;transition:border-color .3s;}
input:focus,select:focus{border-color:var(--primary);}
.btn-submit{margin-top:18px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;border:none;padding:11px 22px;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:500;transition:all .3s;box-shadow:0 4px 12px rgba(163,22,33,.25);}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(163,22,33,.35);}
.btn-cancel{display:inline-block;margin-top:18px;margin-left:8px;background:#888;color:white;border:none;padding:11px 22px;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:500;text-decoration:none;transition:all .3s;}
.btn-cancel:hover{background:#666;}
table{width:100%;background:white;border-collapse:separate;border-spacing:0;border-radius:var(--radius);overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);}
th{background:var(--primary-dark);color:white;padding:12px 14px;font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.5px;text-align:left;}
td{padding:10px 14px;border-bottom:1px solid #f0eeeb;color:#555;font-size:13px;vertical-align:middle;}
tbody tr:hover td{background:#faf8f6;}
.menu-img{width:50px;height:50px;object-fit:cover;border-radius:6px;display:block;}
.btn{padding:5px 12px;border-radius:6px;text-decoration:none;color:white;font-size:11px;display:inline-block;margin:1px;transition:all .2s;font-weight:500;border:none;cursor:pointer;}
.btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.15);}
.btn-edit{background:#1976d2;}
.btn-hapus{background:#c0392b;}
.msg{padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;}
.msg.success{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;}
.msg.error{background:#fff5f5;color:#c0392b;border:1px solid #fdd;}
.empty{text-align:center;padding:30px;color:#999;font-size:14px;}
</style>
</head>
<body>

<div class="sidebar">
<div class="logo">D'LAROZ</div>
<a href="dashboard.php">Dashboard</a>
<a href="menu.php" class="active">Kelola Menu</a>
<a href="pesanan.php">Pesanan</a>
<?php if($_SESSION['role'] === 'admin'): ?>
<a href="payment_settings.php">Pengaturan Pembayaran</a>
<a href="laporan.php">Laporan Bulanan</a>
<a href="qrcode_tables.php">QR Code Meja</a>
<a href="../index.php">Lihat Menu</a>
<?php endif; ?>
<a href="logout.php">Logout</a>
</div>

<div class="content">

<h1>Kelola Menu</h1>
<br>

<?php if($message): ?>
<div class="msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="layout">

<div>
<div class="card">
<h2><?php echo $edit_data ? 'Edit Menu' : 'Tambah Menu Baru'; ?></h2>
<form method="POST" enctype="multipart/form-data">
<?php if($edit_data): ?>
<input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
<?php endif; ?>

<label>Nama Menu</label>
<input type="text" name="nama_menu" value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_menu']) : ''; ?>" required>

<label>Kategori</label>
<input type="text" name="kategori" list="kategori_list" value="<?php echo $edit_data ? htmlspecialchars($edit_data['kategori']) : ''; ?>" required>
<datalist id="kategori_list">
<?php foreach($all_kategoris as $k): ?>
<option value="<?php echo htmlspecialchars($k); ?>">
<?php endforeach; ?>
</datalist>

<label>Harga (Rp)</label>
<input type="number" name="harga" value="<?php echo $edit_data ? $edit_data['harga'] : ''; ?>" min="0" required>

<label>Gambar</label>
<input type="file" name="gambar" accept="image/jpg,image/jpeg,image/png,image/webp">
<?php if($edit_data && !empty($edit_data['gambar'])): ?>
<div style="margin-top:8px;font-size:12px;color:#888;">
Gambar saat ini: <strong><?php echo $edit_data['gambar']; ?></strong> (biarkan kosong jika tidak ingin mengganti)
</div>
<?php endif; ?>

<div style="display:flex;align-items:center;gap:8px;">
<button type="submit" name="simpan" class="btn-submit"><?php echo $edit_data ? 'Simpan Perubahan' : 'Tambah Menu'; ?></button>
<?php if($edit_data): ?>
<a href="menu.php" class="btn-cancel">Batal</a>
<?php endif; ?>
</div>
</form>
</div>
</div>

<div>
<div class="card" style="padding:0;overflow:hidden;">
<h2 style="padding:24px 24px 0;">Daftar Menu</h2>
<div style="overflow-x:auto;">
<table>
<thead>
<tr><th>Gambar</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Aksi</th></tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($menu_items) == 0): ?>
<tr><td colspan="5" class="empty">Belum ada menu.</td></tr>
<?php endif; ?>
<?php while($row = mysqli_fetch_assoc($menu_items)): ?>
<tr>
<td>
<?php if(!empty($row['gambar'])): ?>
<img src="../assets/img/<?php echo $row['gambar']; ?>" class="menu-img" alt="<?php echo htmlspecialchars($row['nama_menu']); ?>">
<?php else: ?>
<div style="width:50px;height:50px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#bbb;font-size:10px;">NoImg</div>
<?php endif; ?>
</td>
<td><?php echo htmlspecialchars($row['nama_menu']); ?></td>
<td><?php echo htmlspecialchars($row['kategori']); ?></td>
<td>Rp <?php echo number_format($row['harga'],0,',','.'); ?></td>
<td>
<a href="menu.php?edit=<?php echo $row['id']; ?>" class="btn btn-edit">Edit</a>
<a href="menu.php?hapus=<?php echo $row['id']; ?>" class="btn btn-hapus" onclick="return confirm('Hapus menu <?php echo htmlspecialchars(addslashes($row['nama_menu'])); ?>?')">Hapus</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>

</div>

</div>

</body>
</html>
