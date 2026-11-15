<?php
session_start();
include "../config/database.php";

// Memastikan hanya admin yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

//mengambil produk dan kategori produk
$query = "SELECT products.*, categories.nama_kategori
          FROM products
          LEFT JOIN categories
          ON products.category_id = categories.id
          ORDER BY products.id DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk</title>
</head>
<body>

<h1>Kelola Produk TokoKita</h1>

<a href="index.php">Dashboard</a> |
<a href="../auth/logout.php">Logout</a>

<br><br>

<a href="tambah_produk.php">+ Tambah Produk</a>

<br><br>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>No</th>
        <th>Gambar</th>
        <th>Nama Produk</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Aksi</th>
    </tr>

    <?php
    $no = 1;

    while ($produk = mysqli_fetch_assoc($result)) {
    ?>

    <tr>
        <td><?php echo $no++; ?></td>

        <td>
            <?php if (!empty($produk['gambar'])) { ?>
                <img src="../uploads/<?php echo htmlspecialchars($produk['gambar']); ?>"
                     width="70">
            <?php } else { ?>
                Tidak ada gambar
            <?php } ?>
        </td>

        <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>

        <td><?php echo htmlspecialchars($produk['nama_kategori'] ?? '-'); ?></td>

        <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>

        <td><?php echo $produk['stok']; ?></td>

        <td>
            <a href="edit_produk.php?id=<?php echo $produk['id']; ?>">Edit</a>

            <form action="hapus_produk.php" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                <input type="hidden" name="id" value="<?php echo $produk['id']; ?>">
                <button type="submit">Hapus</button>
            </form>
        </td>
    </tr>

    <?php } ?>

</table>

</body>
</html>