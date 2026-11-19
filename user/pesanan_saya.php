
<?php
session_start();

include "../config/database.php";

// Cek login user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Ambil semua pesanan milik user
$query = "SELECT * FROM orders
          WHERE user_id = ?
          ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya</title>
</head>
<body>

    <h1>Riwayat Pesanan Saya</h1>

    <p>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>

    <a href="dashboard.php">Beranda</a> |
    <a href="keranjang.php">Keranjang</a> |
    <a href="../auth/logout.php">Logout</a>

    <hr>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <?php while ($pesanan = mysqli_fetch_assoc($result)) { ?>

            <h3>Pesanan #<?php echo $pesanan['id']; ?></h3>

            <p>
                Tanggal:
                <?php echo htmlspecialchars($pesanan['created_at']); ?>
            </p>

            <p>
                Status:
                <?php echo htmlspecialchars($pesanan['status']); ?>
            </p>

            <p>
                Alamat Pengiriman:
                <?php echo htmlspecialchars($pesanan['alamat_pengiriman']); ?>
            </p>

            <p>
                Nomor HP:
                <?php echo htmlspecialchars($pesanan['no_hp']); ?>
            </p>

            <p>
                Metode Pembayaran:
                <?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?>
            </p>

            <h4>Produk yang Dibeli:</h4>

            <table border="1" cellpadding="8">
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>

                <?php
                $order_id = $pesanan['id'];

                $query_item = "SELECT * FROM order_items
                               WHERE order_id = ?";

                $stmt_item = mysqli_prepare($conn, $query_item);
                mysqli_stmt_bind_param($stmt_item, "i", $order_id);
                mysqli_stmt_execute($stmt_item);

                $result_item = mysqli_stmt_get_result($stmt_item);

                while ($item = mysqli_fetch_assoc($result_item)) {
                ?>

                    <tr>
                        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                        <td>Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $item['jumlah']; ?></td>
                        <td>Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    </tr>

                <?php } ?>

            </table>

            <h4>
                Total: Rp<?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?>
            </h4>

            <hr>

        <?php } ?>

    <?php } else { ?>

        <p>Belum ada pesanan.</p>

    <?php } ?>

</body>
</html>
