
<?php
session_start();

include "../config/database.php";

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus pengguna
if (isset($_POST['hapus'])) {

    $user_id = (int) $_POST['user_id'];

    // Pastikan yang dihapus adalah akun user, bukan admin
    $query_cek = "SELECT id FROM users WHERE id = ? AND role = 'user'";
    $stmt_cek = mysqli_prepare($conn, $query_cek);
    mysqli_stmt_bind_param($stmt_cek, "i", $user_id);
    mysqli_stmt_execute($stmt_cek);

    $hasil_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($hasil_cek) > 0) {

        $query_hapus = "DELETE FROM users WHERE id = ? AND role = 'user'";
        $stmt_hapus = mysqli_prepare($conn, $query_hapus);
        mysqli_stmt_bind_param($stmt_hapus, "i", $user_id);
        mysqli_stmt_execute($stmt_hapus);
    }

    header("Location: pengguna.php");
    exit;
}

// Ambil semua data pengguna
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <h1>Kelola Pengguna TokoKita</h1>

    <p>
        Selamat datang, Admin
        <?php echo htmlspecialchars($_SESSION['nama']); ?>!
    </p>

    <a href="index.php">Dashboard Admin</a> |
    <a href="produk.php">Kelola Produk</a> |
    <a href="pesanan.php">Kelola Pesanan</a> |
    <a href="../auth/logout.php">Logout</a>

    <hr>

    <h2>Daftar Pengguna</h2>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Nomor HP</th>
                <th>Alamat</th>
                <th>Role</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>

            <?php while ($user = mysqli_fetch_assoc($result)) { ?>

                <tr>
                    <td><?php echo $user['id']; ?></td>

                    <td><?php echo htmlspecialchars($user['nama']); ?></td>

                    <td><?php echo htmlspecialchars($user['email']); ?></td>

                    <td><?php echo htmlspecialchars($user['no_hp']); ?></td>

                    <td><?php echo htmlspecialchars($user['alamat']); ?></td>

                    <td><?php echo htmlspecialchars($user['role']); ?></td>

                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>

                    <td>
                        <?php if ($user['role'] == 'user') { ?>

                            <form method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">

                                <input type="hidden" name="user_id"
                                       value="<?php echo $user['id']; ?>">

                                <button type="submit" name="hapus">
                                    Hapus
                                </button>

                            </form>

                        <?php } else { ?>

                            Tidak ada aksi

                        <?php } ?>
                    </td>
                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p>Belum ada pengguna yang terdaftar.</p>

    <?php } ?>

</body>
</html>
