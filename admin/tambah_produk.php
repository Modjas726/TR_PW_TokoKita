<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama_produk']);
    $kategori = (int) $_POST['category_id'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    $gambar = "";

    // Proses upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] != 4) {

        $ekstensi = strtolower(pathinfo(
            $_FILES['gambar']['name'],
            PATHINFO_EXTENSION
        ));

        $format = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ekstensi, $format)) {
            $pesan = "Format gambar tidak diperbolehkan!";
        } elseif ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
            $pesan = "Ukuran gambar maksimal 2 MB!";
        } else {
            $gambar = uniqid('produk_') . '.' . $ekstensi;

            if (!move_uploaded_file(
                $_FILES['gambar']['tmp_name'],
                "../uploads/" . $gambar
            )) {
                $pesan = "Upload gambar gagal!";
            }
        }
    }

    if ($pesan == "") {
        $query = "INSERT INTO products
                  (category_id, nama_produk, deskripsi, harga, stok, gambar)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);

        mysqli_stmt_bind_param(
            $stmt, "issdis",
            $kategori, $nama, $deskripsi,
            $harga, $stok, $gambar
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: produk.php");
            exit;
        } else {
            $pesan = "Gagal menambahkan produk!";
            if ($gambar != "" && file_exists("../uploads/" . $gambar)) {
                unlink("../uploads/" . $gambar);
            }
        }
    }
}

$kategori = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
</head>
<body>

<h2>Tambah Produk Baru</h2>

<p><?php echo htmlspecialchars($pesan); ?></p>

<form method="POST" enctype="multipart/form-data">

    <label>Nama Produk</label><br>
    <input type="text" name="nama_produk" required><br><br>

    <label>Kategori</label><br>
    <select name="category_id" required>
        <option value="">Pilih Kategori</option>

        <?php while ($k = mysqli_fetch_assoc($kategori)) { ?>
            <option value="<?php echo $k['id']; ?>">
                <?php echo htmlspecialchars($k['nama_kategori']); ?>
            </option>
        <?php } ?>

    </select><br><br>

    <label>Deskripsi</label><br>
    <textarea name="deskripsi" required></textarea><br><br>

    <label>Harga</label><br>
    <input type="number" name="harga" min="0" step="1" required><br><br>

    <label>Stok</label><br>
    <input type="number" name="stok" min="0" required><br><br>

    <label>Gambar Produk</label><br>
    <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"><br><br>

    <button type="submit" name="simpan">Simpan Produk</button>

</form>

<br>
<a href="produk.php">Kembali</a>

</body>
</html>