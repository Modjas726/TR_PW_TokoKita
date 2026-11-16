
<?php
session_start();
include "../config/database.php";

// Cek apakah yang login adalah Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Menghitung jumlah data
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"));
$pengguna = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='user'"));
$pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"));

// Mengambil 5 pesanan terbaru
$query_pesanan = mysqli_query($conn, "
    SELECT orders.*, users.nama
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - TokoKita</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        .navbar {
            background: #2563eb;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            font-size: 24px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 10px 16px;
            border-radius: 6px;
        }

        .container {
            padding: 35px 40px;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .welcome h1 {
            margin-bottom: 10px;
        }

        .statistik {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #00000010;
        }

        .card h3 {
            color: #666;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .card h1 {
            color: #2563eb;
            font-size: 32px;
        }

        .menu {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }

        .menu a {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 7px;
        }

        .menu a:hover {
            background: #1d4ed8;
        }

        .tabel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            overflow-x: auto;
        }

        .tabel h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f1f5f9;
        }

        @media (max-width: 700px) {
            .statistik {
                grid-template-columns: 1fr;
            }

            .navbar {
                padding: 20px;
            }

            .container {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<div class="navbar">
    <h2>TokoKita Admin</h2>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="container">

    <div class="welcome">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>
        <p>Anda login sebagai Administrator TokoKita.</p>
    </div>

    <div class="statistik">

        <div class="card">
            <h3>Total Produk</h3>
            <h1><?php echo $produk['total']; ?></h1>
        </div>

        <div class="card">
            <h3>Total Pengguna</h3>
            <h1><?php echo $pengguna['total']; ?></h1>
        </div>

        <div class="card">
            <h3>Total Pesanan</h3>
            <h1><?php echo $pesanan['total']; ?></h1>
        </div>

    </div>

    <h2 style="margin-bottom: 15px;">Menu Admin</h2>

    <div class="menu">
        <a href="produk.php">Kelola Produk</a>
        <a href="pesanan.php">Kelola Pesanan</a>
        <a href="pengguna.php">Data Pengguna</a>
    </div>

    <div class="tabel">
        <h2>Pesanan Terbaru</h2>

        <table>
            <tr>
                <th>No.</th>
                <th>Nama Pengguna</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>

            <?php
            $no = 1;

            if (mysqli_num_rows($query_pesanan) > 0) {
                while ($row = mysqli_fetch_assoc($query_pesanan)) {
            ?>

            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['nama'] ?? 'Pengguna tidak ditemukan'); ?></td>
                <td>Rp<?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>

            <?php
                }
            } else {
                echo "<tr><td colspan='5'>Belum ada pesanan.</td></tr>";
            }
            ?>

        </table>
    </div>

</div>

</body>
</html>
