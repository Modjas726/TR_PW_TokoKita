
<?php
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include "../config/database.php";

// Cek login User
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['id'];
$pesan = "";

// Mengambil keranjang User
function ambilKeranjang($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT cart_items.product_id,
               cart_items.jumlah,
               products.nama_produk,
               products.harga,
               products.stok
        FROM cart
        JOIN cart_items ON cart.id = cart_items.cart_id
        JOIN products ON cart_items.product_id = products.id
        WHERE cart.user_id = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

// Proses checkout ketika tombol diklik
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $alamat = trim($_POST['alamat'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $metode = $_POST['metode_pembayaran'] ?? '';

    $metode_valid = ['Transfer Bank', 'COD'];

    if ($alamat == '' || $no_hp == '' || !in_array($metode, $metode_valid, true)) {
        $pesan = "Lengkapi data checkout dengan benar.";
    } else {

        try {
            mysqli_begin_transaction($conn);

            // Ambil isi keranjang dan kunci data produk
            $stmt = mysqli_prepare($conn, "
                SELECT cart_items.product_id,
                       cart_items.jumlah,
                       products.nama_produk,
                       products.harga,
                       products.stok
                FROM cart
                JOIN cart_items ON cart.id = cart_items.cart_id
                JOIN products ON cart_items.product_id = products.id
                WHERE cart.user_id = ?
                FOR UPDATE
            ");

            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $items = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if (count($items) == 0) {
                throw new Exception("Keranjang masih kosong.");
            }

            $total = 0;

            // Periksa stok dan hitung total
            foreach ($items as $item) {
                if ($item['stok'] < $item['jumlah']) {
                    throw new Exception(
                        "Stok produk " . $item['nama_produk'] . " tidak mencukupi."
                    );
                }

                $total += $item['harga'] * $item['jumlah'];
            }

            // Simpan data pesanan
            $stmt = mysqli_prepare($conn, "
                INSERT INTO orders
                (user_id, total_harga, alamat_pengiriman, no_hp, metode_pembayaran)
                VALUES (?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param(
                $stmt, "idsss",
                $user_id, $total, $alamat, $no_hp, $metode
            );

            mysqli_stmt_execute($stmt);

            $order_id = mysqli_insert_id($conn);

            // Simpan detail pesanan dan kurangi stok
            foreach ($items as $item) {

                $product_id = $item['product_id'];
                $nama = $item['nama_produk'];
                $harga = $item['harga'];
                $jumlah = $item['jumlah'];
                $subtotal = $harga * $jumlah;

                $stmt = mysqli_prepare($conn, "
                    INSERT INTO order_items
                    (order_id, product_id, nama_produk, harga, jumlah, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                mysqli_stmt_bind_param(
                    $stmt, "iisdid",
                    $order_id, $product_id, $nama,
                    $harga, $jumlah, $subtotal
                );

                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "
                    UPDATE products
                    SET stok = stok - ?
                    WHERE id = ? AND stok >= ?
                ");

                mysqli_stmt_bind_param(
                    $stmt, "iii",
                    $jumlah, $product_id, $jumlah
                );

                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) != 1) {
                    throw new Exception("Gagal memperbarui stok produk.");
                }
            }

            // Kosongkan keranjang setelah pesanan berhasil
            $stmt = mysqli_prepare($conn, "
                DELETE cart_items
                FROM cart_items
                JOIN cart ON cart_items.cart_id = cart.id
                WHERE cart.user_id = ?
            ");

            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);

            header("Location: pesanan_saya.php");
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $pesan = $e->getMessage();
        }
    }
}

// Mengambil data keranjang untuk ditampilkan
$result = ambilKeranjang($conn, $user_id);
$total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - TokoKita</title>
</head>
<body>

<h1>Checkout TokoKita</h1>

<p>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>

<a href="keranjang.php">Kembali ke Keranjang</a>

<hr>

<?php if ($pesan != '') { ?>
    <p><?php echo htmlspecialchars($pesan); ?></p>
<?php } ?>

<h2>Ringkasan Pesanan</h2>

<?php if (mysqli_num_rows($result) > 0) { ?>

<table border="1" cellpadding="10">
    <tr>
        <th>Produk</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Subtotal</th>
    </tr>

    <?php while ($item = mysqli_fetch_assoc($result)) {
        $subtotal = $item['harga'] * $item['jumlah'];
        $total += $subtotal;
    ?>

    <tr>
        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
        <td>Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
        <td><?php echo $item['jumlah']; ?></td>
        <td>Rp<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
    </tr>

    <?php } ?>
</table>

<h3>Total Pembayaran:
    Rp<?php echo number_format($total, 0, ',', '.'); ?>
</h3>

<hr>

<h2>Data Pengiriman</h2>

<form method="POST">

    <label>Alamat Pengiriman:</label><br>
    <textarea name="alamat" rows="4" required></textarea>

    <br><br>

    <label>Nomor HP:</label><br>
    <input type="text" name="no_hp" required>

    <br><br>

    <label>Metode Pembayaran:</label><br>

    <select name="metode_pembayaran" required>
        <option value="">Pilih Pembayaran</option>
        <option value="Transfer Bank">Transfer Bank</option>
        <option value="COD">COD (Bayar di Tempat)</option>
    </select>

    <br><br>

    <button type="submit">Buat Pesanan</button>

</form>

<?php } else { ?>

    <p>Keranjang masih kosong. Silakan pilih produk terlebih dahulu.</p>
    <a href="dashboard.php">Belanja Sekarang</a>

<?php } ?>

</body>
</html>
