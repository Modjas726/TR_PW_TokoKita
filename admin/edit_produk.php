<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Mengambil produk berdasarkan ID
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    die("Produk tidak ditemukan!");
}

$pesan = "";

if (isset($_POST['update'])) {
    $nama = trim($_POST['nama_produk']);
    $kategori = (int) $_POST['category_id'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    $gambar = $produk['gambar'];
    $gambarBaru = "";

    // Jika admin memilih gambar baru
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
            $gambarBaru = uniqid('produk_') . '.' . $ekstensi;

            if (move_uploaded_file(
                $_FILES['gambar']['tmp_name'],
                "../uploads/" . $gambarBaru
            )) {
                $gambar = $gambarBaru;
            } else {
                $pesan = "Upload gambar gagal!";
            }
        }
    }

    if ($pesan == "") {
        $query = "UPDATE products SET
                  category_id = ?,
                  nama_produk = ?,
                  deskripsi = ?,
                  harga = ?,
                  stok = ?,
                  gambar = ?
                  WHERE id = ?";

        $stmt = mysqli_prepare($conn, $query);

        mysqli_stmt_bind_param(
            $stmt, "issdisi",
            $kategori, $nama, $deskripsi,
            $harga, $stok, $gambar, $id
        );

        if (mysqli_stmt_execute($stmt)) {

            // Menghapus gambar lama setelah update berhasil
            if ($gambarBaru != "" && $produk['gambar'] != "") {
                $fileLama = "../uploads/" . basename($produk['gambar']);

                if (file_exists($fileLama)) {
                    unlink($fileLama);
                }
            }

            header("Location: produk.php");
            exit;

        } else {
            $pesan = "Gagal mengubah produk!";

            if ($gambarBaru != "") {
                unlink("../uploads/" . $gambarBaru);
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
    <title>Edit Produk</title>
</head>
<body>

<h2>Edit Produk</h2>

<p><?php echo htmlspecialchars($pesan); ?></p>

<form method="POST" enctype="multipart/form-data">

    <label>Nama Produk</label><br>
    <input type="text" name="nama_produk"
    value="<?php echo htmlspecialchars($produk['nama_produk']); ?>"
    required><br><br>

    <label>Kategori</label><br>
    <select name="category_id" required>
        <?php while ($k = mysqli_fetch_assoc($kategori)) { ?>
            <option value="<?php echo $k['id']; ?>"
                <?php echo ($k['id'] == $produk['category_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($k['nama_kategori']); ?>
            </option>
        <?php } ?>
    </select><br><br>

    <label>Deskripsi</label><br>
    <textarea name="deskripsi" required><?php
        echo htmlspecialchars($produk['deskripsi']);
    ?></textarea><br><br>

    <label>Harga</label><br>
    <input type="number" name="harga"
    value="<?php echo $produk['harga']; ?>"
    min="0" step="1" required><br><br>

    <label>Stok</label><br>
    <input type="number" name="stok"
    value="<?php echo $produk['stok']; ?>"
    min="0" required><br><br>

    <label>Gambar Saat Ini</label><br>

    <?php if (!empty($produk['gambar'])) { ?>
        <img src="../uploads/<?php echo htmlspecialchars($produk['gambar']); ?>"
             width="100"><br>
    <?php } else { ?>
        Belum ada gambar<br>
    <?php } ?>

    <br>

    <label>Ganti Gambar</label><br>
    <input type="file" name="gambar"
           accept=".jpg,.jpeg,.png,.webp"><br><br>

    <button type="submit" name="update">Update Produk</button>

</form>

<br>
<a href="produk.php">Kembali</a>

</body>
</html>