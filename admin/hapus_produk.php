<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = (int) $_POST['id'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT gambar FROM products WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);

    if ($produk) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM products WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            if (!empty($produk['gambar'])) {
                $file = "../uploads/" . basename($produk['gambar']);

                if (file_exists($file)) {
                    unlink($file);
                }
            }

            header("Location: produk.php");
            exit;

        } else {
            echo "Produk tidak dapat dihapus. Produk mungkin sudah digunakan dalam transaksi.";
        }

    } else {
        echo "Produk tidak ditemukan!";
    }

} else {
    header("Location: produk.php");
    exit;
}
?>
<br>
<a href="produk.php">Kembali ke produk</a>