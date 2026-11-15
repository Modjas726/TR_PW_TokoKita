
<?php
session_start();
include "../config/database.php";

$pesan = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    //nyari akun dari emial
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    //cek pass
    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        //dari role user atau admin
        if ($user['role'] == "admin") {
            header("Location: ../admin/index.php");
            exit;
        } else {
            header("Location: ../user/dashboard.php");
            exit;
        }

    } else {
        $pesan = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login TokoKita</title>
</head>
<body>

<h2>Login TokoKita</h2>

<p><?php echo $pesan; ?></p>

<form method="POST">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="login">Login</button>
</form>

<p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>

</body>
</html>
