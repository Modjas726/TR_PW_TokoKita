<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {

    $item_id = (int) $_POST['id'];
    $user_id = $_SESSION['id'];

    // Hanya menghapus item milik keranjang User yang login
    $stmt = mysqli_prepare($conn, "
        DELETE cart_items FROM cart_items
        JOIN cart ON cart_items.cart_id = cart.id
        WHERE cart_items.id = ? AND cart.user_id = ?
    ");

    mysqli_stmt_bind_param($stmt, "ii", $item_id, $user_id);
    mysqli_stmt_execute($stmt);
}

header("Location: keranjang.php");
exit;
?>