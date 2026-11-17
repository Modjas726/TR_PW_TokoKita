<?php
session_start();
include "../config/database.php";

// Cek login User
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Mengambil keranjang User
$query = mysqli_prepare($conn, "
    SELECT cart.id
    FROM cart
    WHERE cart.user_id = ?
");
mysqli_stmt_bind_param($query, "i", $user_id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$cart = mysqli_fetch_assoc($result);

$total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - TokoKita</title>
</head>
<body>

<h1>Keranjang Belanja</h1>

<p>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>

<a href="dashboard.php">Kembali ke Katalog</a>

<hr>

<?php
if ($cart) {

    $cart_id = $cart['id'];

    $query = mysqli_prepare($conn, "
        SELECT cart_items.id, cart_items.jumlah,
               products.nama_produk, products.harga,
               products.stok
        FROM cart_items
        JOIN products ON cart_items.product_id = products.id
        WHERE cart_items.cart_id = ?
    ");

    mysqli_stmt_bind_param($query, "i", $cart_id);
    mysqli_stmt_execute($query);
    $items = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($items) > 0) {
?>

<table border="1" cellpadding="10">
    <tr>
        <th>Nama Produk</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Subtotal</th>
        <th>Aksi</th>
    </tr>

    <?php while ($item = mysqli_fetch_assoc($items)) {
        $subtotal = $item['harga'] * $item['jumlah'];
        $total += $subtotal;
    ?>

    <tr>
        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>

        <td>Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?></td>

        <td><?php echo $item['jumlah']; ?></td>

        <td>Rp<?php echo number_format($subtotal, 0, ',', '.'); ?></td>

        <td>
            <form action="hapus_keranjang.php" method="POST"
                  onsubmit="return confirm('Hapus produk ini?')">
                <input type="hidden" name="id"
                       value="<?php echo $item['id']; ?>">
                <button type="submit">Hapus</button>
            </form>
        </td>
    </tr>

    <?php } ?>

</table>

<h3>Total Belanja: Rp<?php echo number_format($total, 0, ',', '.'); ?></h3>

<?php
    } else {
        echo "<p>Keranjang masih kosong.</p>";
    }

} else {
    echo "<p>Keranjang masih kosong.</p>";
}
?>

</body>
</html>