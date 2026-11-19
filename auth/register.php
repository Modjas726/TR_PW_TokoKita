
<?php
include "../config/database.php";

$pesan = "";

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter!";
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

        if (mysqli_num_rows($cek) > 0) {
            $pesan = "Email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (nama, email, password, no_hp, alamat, role)
                    VALUES ('$nama', '$email', '$hash', '$no_hp', '$alamat', 'user')";

            if (mysqli_query($conn, $query)) {
                $pesan = "Registrasi berhasil! Silakan login.";
            } else {
                $pesan = "Registrasi gagal!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi TokoKita</title>
</head>
<body>

<h2>Registrasi TokoKita</h2>

<p><?php echo $pesan; ?></p>

<form method="POST">
    <label>Nama Lengkap</label><br>
    <input type="text" name="nama" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" required><br><br>
    
    <label>Nomor HP</label>
    <input type="text" name="no_hp" required>

    <label>Alamat</label>
    <textarea name="alamat" required></textarea>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="register">Daftar</button>
</form>

<p>Sudah punya akun? <a href="login.php">Login</a></p>

</body>
</html>
