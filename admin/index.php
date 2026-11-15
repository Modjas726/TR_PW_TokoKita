
<?php
session_start();

//cek apakah rolenya admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
</head>
<body>

<h1>Dashboard Admin TokoKita</h1>

<h3>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h3>

<p>Anda login sebagai Admin.</p>

<ul>
    <li><a href="produk.php">Kelola Produk</a></li>
    <li><a href="pesanan.php">Kelola Pesanan</a></li>
    <li><a href="pengguna.php">Data Pengguna</a></li>
</ul>

<a href="../auth/logout.php">Logout</a>

</body>
</html>
