
<?php
session_start();

include "../config/database.php";

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses perubahan status pesanan
if (isset($_POST['ubah_status'])) {

    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $daftar_status = [
        'Menunggu',
        'Diproses',
        'Dikirim',
        'Selesai',
        'Dibatalkan'
    ];

    if (in_array($status, $daftar_status)) {

        $query_update = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);

        mysqli_stmt_bind_param($stmt_update, "si", $status, $order_id);
        mysqli_stmt_execute($stmt_update);

        header("Location: pesanan.php");
        exit;
    }
}

// Ambil semua pesanan
$query = "SELECT orders.*, users.nama
          FROM orders
          JOIN users ON orders.user_id = users.id
          ORDER BY orders.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <h1>Kelola Pesanan TokoKita</h1>

    <p>Selamat datang, Admin <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>

    <a href="index.php">Dashboard Admin</a> |
    <a href="produk.php">Kelola Produk</a> |
    <a href="../auth/logout.php">Logout</a>

    <hr>

    <h2>Daftar Semua Pesanan</h2>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <table border="1" cellpadding="8">
            <tr>
                <th>ID Pesanan</th>
                <th>Nama Pengguna</th>
                <th>Tanggal</th>
                <th>Total Harga</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php while ($pesanan = mysqli_fetch_assoc($result)) { ?>

                <tr>
                    <td><?php echo $pesanan['id']; ?></td>

                    <td><?php echo htmlspecialchars($pesanan['nama']); ?></td>

                    <td><?php echo htmlspecialchars($pesanan['created_at']); ?></td>

                    <td>
                        Rp<?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($pesanan['status']); ?>
                    </td>

                    <td>
                        <form method="POST">

                            <input type="hidden" name="order_id"
                                   value="<?php echo $pesanan['id']; ?>">

                            <select name="status">
                                <option value="Menunggu"
                                    <?php if ($pesanan['status'] == 'Menunggu') echo 'selected'; ?>>
                                    Menunggu
                                </option>

                                <option value="Diproses"
                                    <?php if ($pesanan['status'] == 'Diproses') echo 'selected'; ?>>
                                    Diproses
                                </option>

                                <option value="Dikirim"
                                    <?php if ($pesanan['status'] == 'Dikirim') echo 'selected'; ?>>
                                    Dikirim
                                </option>

                                <option value="Selesai"
                                    <?php if ($pesanan['status'] == 'Selesai') echo 'selected'; ?>>
                                    Selesai
                                </option>

                                <option value="Dibatalkan"
                                    <?php if ($pesanan['status'] == 'Dibatalkan') echo 'selected'; ?>>
                                    Dibatalkan
                                </option>
                            </select>

                            <button type="submit" name="ubah_status">
                                Simpan
                            </button>

                        </form>
                    </td>
                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p>Belum ada pesanan dari pengguna.</p>

    <?php } ?>

</body>
</html>
