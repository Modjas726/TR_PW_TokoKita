
<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
</head>
<body>

<h1>Selamat Datang di TokoKita!</h1>

<h3>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h3>

<p>Selamat berbelanja di TokoKita.</p>

<ul>
    <li><a href="dashboard.php">Beranda</a></li>
    <li><a href="keranjang.php">Keranjang Belanja</a></li>
    <li><a href="pesanan_saya.php">Pesanan Saya</a></li>
</ul>

<a href="../auth/logout.php">Logout</a>

</body>
</html>
