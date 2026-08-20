<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['login'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $selected_role = $_POST['role'] ?? '';

    $cek = mysqli_query(
        $conn,
        "SELECT * FROM admin
        WHERE username='$username'
        AND password='$password'"
    );

    if(mysqli_num_rows($cek) > 0){

        $data = mysqli_fetch_assoc($cek);

        if($data['role'] !== $selected_role){
            $error = "Akun ini bukan sebagai " . ucfirst($selected_role) . "!";
        }else{
            $_SESSION['admin'] = $username;
            $_SESSION['role'] = $data['role'];

            header("Location: dashboard.php");
            exit;
        }

    }else{

        $error = "Username atau Password Salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Login D'Laroz Cafe</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#a31621;
    --primary-dark:#6d1b1b;
    --primary-light:#c0392b;
    --radius:16px;
    --radius-sm:10px;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

@keyframes fadeInUp{from{transform:translateY(40px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

body{
    background:linear-gradient(135deg,#3a0808,#6d1b1b,#a31621);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

.login-box{
    background:white;
    width:420px;
    padding:40px;
    border-radius:var(--radius);
    box-shadow:0 20px 60px rgba(0,0,0,.3);
    animation:fadeInUp .7s ease;
    position:relative;
    overflow:hidden;
}
.login-box::before{
    content:'';
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:4px;
    background:linear-gradient(90deg,var(--primary-dark),var(--primary),var(--primary-light));
}

h1{
    text-align:center;
    color:var(--primary);
    font-size:26px;
    margin-bottom:4px;
    letter-spacing:1px;
}

.subtitle{
    text-align:center;
    color:#999;
    font-size:13px;
    margin-bottom:28px;
}

input, select{
    width:100%;
    padding:12px 16px;
    margin-top:12px;
    border:2px solid #eee;
    border-radius:var(--radius-sm);
    font-size:14px;
    transition:border-color .3s, box-shadow .3s;
    outline:none;
    background:white;
}
input:focus, select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(163,22,33,.1);}

button{
    width:100%;
    margin-top:24px;
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    color:white;
    border:none;
    padding:14px;
    border-radius:var(--radius-sm);
    cursor:pointer;
    font-size:15px;
    font-weight:600;
    letter-spacing:.5px;
    transition:transform .3s, box-shadow .3s;
    box-shadow:0 4px 15px rgba(163,22,33,.3);
}
button:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(163,22,33,.4);}
button:active{transform:translateY(0);}

.error{
    background:#fff5f5;
    color:#c0392b;
    padding:12px 16px;
    border-radius:var(--radius-sm);
    margin-bottom:16px;
    text-align:center;
    font-size:13px;
    border:1px solid #fdd;
}

</style>

</head>
<body>

<div class="login-box">

<h1>D'LAROZ</h1>

<p class="subtitle">
Pilih role dan masukkan akun Anda
</p>

<?php
if(isset($error)){
?>
<div class="error">
<?php echo $error; ?>
</div>
<?php
}
?>

<form method="POST">

<select name="role" required>
<option value="">Pilih Role</option>
<option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
<option value="kasir" <?php echo (isset($_POST['role']) && $_POST['role'] === 'kasir') ? 'selected' : ''; ?>>Kasir</option>
</select>

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button
type="submit"
name="login">

Login

</button>

</form>

</div>

</body>
</html>