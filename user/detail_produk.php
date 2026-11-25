<?php
session_start();
include "../config/database.php";

// Cek login User
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek ID produk
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID produk tidak valid.";
    exit;
}

$id = (int) $_GET['id'];

// Mengambil data produk
$query = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$produk = mysqli_fetch_assoc($result);

// Jika produk tidak ditemukan
if (!$produk) {
    echo "Produk tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - TokoKita</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Detail Produk</h1>

<h3>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h3>

<?php if (!empty($produk['gambar'])) { ?>
    <img
        src="../uploads/<?php echo htmlspecialchars($produk['gambar']); ?>"
        alt="Gambar produk"
        width="200"
    >
<?php } ?>

<h2><?php echo htmlspecialchars($produk['nama_produk']); ?></h2>

<p>Harga: Rp<?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>

<p>Stok: <?php echo $produk['stok']; ?></p>

<h3>Deskripsi Produk</h3>

<p>
    <?php echo nl2br(htmlspecialchars($produk['deskripsi'])); ?>
</p>

<a href="dashboard.php">Kembali ke Katalog</a>

</body>
</html>