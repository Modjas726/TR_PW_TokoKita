<?php
session_start();
include "../config/database.php";

// Cek login User
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek data yang dikirim
if (!isset($_POST['product_id']) || !isset($_POST['jumlah'])) {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['id'];
$product_id = (int) $_POST['product_id'];
$jumlah = (int) $_POST['jumlah'];

if ($product_id <= 0 || $jumlah <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Cek produk dan stok
$stmt = mysqli_prepare($conn, "SELECT stok FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk || $produk['stok'] < $jumlah) {
    echo "Produk tidak tersedia atau stok tidak mencukupi.";
    exit;
}

// Mencari keranjang milik User
$stmt = mysqli_prepare($conn, "SELECT id FROM cart WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cart = mysqli_fetch_assoc($result);

// Jika keranjang belum ada, buat keranjang baru
if (!$cart) {
    $stmt = mysqli_prepare($conn, "INSERT INTO cart (user_id) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $cart_id = mysqli_insert_id($conn);
} else {
    $cart_id = $cart['id'];
}

// Cek apakah produk sudah ada di keranjang
$stmt = mysqli_prepare($conn, "
    SELECT id, jumlah FROM cart_items
    WHERE cart_id = ? AND product_id = ?
");
mysqli_stmt_bind_param($stmt, "ii", $cart_id, $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

if ($item) {
    $jumlah_baru = $item['jumlah'] + $jumlah;

    if ($jumlah_baru > $produk['stok']) {
        echo "Jumlah melebihi stok yang tersedia.";
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        UPDATE cart_items SET jumlah = ? WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, "ii", $jumlah_baru, $item['id']);
    mysqli_stmt_execute($stmt);

} else {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO cart_items (cart_id, product_id, jumlah)
        VALUES (?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "iii", $cart_id, $product_id, $jumlah);
    mysqli_stmt_execute($stmt);
}

// Kembali ke keranjang
header("Location: keranjang.php");
exit;
?>