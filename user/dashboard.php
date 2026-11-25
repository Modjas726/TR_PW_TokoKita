<?php
session_start();
include "../config/database.php";

// Cek apakah yang login adalah User
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Mengambil semua produk dari database
$query = mysqli_query($conn, "
    SELECT * FROM products
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - TokoKita</title>
    <link rel="stylesheet" href="../assets/style.css">
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

<hr>

<h2>Katalog Produk</h2>

<?php
if (mysqli_num_rows($query) > 0) {

    while ($produk = mysqli_fetch_assoc($query)) {
?>

    <div>
        <?php if (!empty($produk['gambar'])) { ?>
            <img
                src="../uploads/<?php echo htmlspecialchars($produk['gambar']); ?>"
                alt="Gambar produk"
                width="150"
            >
        <?php } ?>

        <h3><?php echo htmlspecialchars($produk['nama_produk']); ?></h3>

        <p>Harga: Rp<?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>

        <p>Stok: <?php echo $produk['stok']; ?></p>

        <a href="detail_produk.php?id=<?php echo $produk['id']; ?>">
            Lihat Detail
        </a>

        <form action="tambah_keranjang.php" method="POST">
            <input type="hidden" name="product_id"
                value="<?php echo $produk['id']; ?>">

            <label>Jumlah:</label>
            <input type="number" name="jumlah" value="1"
                min="1" max="<?php echo $produk['stok']; ?>" required>

            <button type="submit">Tambah ke Keranjang</button>
        </form>

    </div>

    <hr>

<?php
    }

} else {
    echo "<p>Belum ada produk tersedia.</p>";
}
?>

</body>
</html>