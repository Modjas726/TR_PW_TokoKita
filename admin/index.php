
<?php
session_start();
include "../config/database.php";

// Cek apakah yang login adalah Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Menghitung jumlah data
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"));
$pengguna = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='user'"));
$pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"));

// Mengambil 5 pesanan terbaru
$query_pesanan = mysqli_query($conn, "
    SELECT orders.*, users.nama
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - TokoKita</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<div class="navbar">
    <h2>TokoKita Admin</h2>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="container">

    <div class="welcome">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>
        <p>Anda login sebagai Administrator TokoKita.</p>
    </div>

    <div class="statistik">

        <div class="card">
            <h3>Total Produk</h3>
            <h1><?php echo $produk['total']; ?></h1>
        </div>

        <div class="card">
            <h3>Total Pengguna</h3>
            <h1><?php echo $pengguna['total']; ?></h1>
        </div>

        <div class="card">
            <h3>Total Pesanan</h3>
            <h1><?php echo $pesanan['total']; ?></h1>
        </div>

    </div>

    <h2 style="margin-bottom: 15px;">Menu Admin</h2>

    <div class="menu">
        <a href="produk.php">Kelola Produk</a>
        <a href="pesanan.php">Kelola Pesanan</a>
        <a href="pengguna.php">Data Pengguna</a>
    </div>

    <div class="tabel">
        <h2>Pesanan Terbaru</h2>

        <table>
            <tr>
                <th>No.</th>
                <th>Nama Pengguna</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>

            <?php
            $no = 1;

            if (mysqli_num_rows($query_pesanan) > 0) {
                while ($row = mysqli_fetch_assoc($query_pesanan)) {
            ?>

            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['nama'] ?? 'Pengguna tidak ditemukan'); ?></td>
                <td>Rp<?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>

            <?php
                }
            } else {
                echo "<tr><td colspan='5'>Belum ada pesanan.</td></tr>";
            }
            ?>

        </table>
    </div>

</div>

</body>
</html>
