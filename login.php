<?php 
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

// Konfigurasi database
$host = 'localhost'; 
$user = 'root'; 
$password = ''; 
$database = 'votehub_webapp'; 

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error()); // Jika koneksi gagal, tampilkan pesan error
}

// Variabel untuk menyimpan pesan error
$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form login
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input: periksa apakah username dan password diisi
    if (empty($username) || empty($password)) {
        $error = "Username and Password are required!";
    } else {
        // Query untuk memeriksa keberadaan username di database
        $stmt = $conn->prepare("SELECT username, password, nama_lengkap FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc(); // Ambil data pengguna dari hasil query

            // Verifikasi password dengan fungsi password_verify
            if (password_verify($password, $user['password'])) {
                // Simpan informasi pengguna ke dalam sesi
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

                // Redirect ke halaman dashboard setelah login berhasil
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!"; // Pesan jika password salah
            }
        } else {
            $error = "Username not found!"; // Pesan jika username tidak ditemukan
        }
        $stmt->close(); // Tutup statement
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votery - Login</title>
    <style>
        /* Gaya tampilan halaman login */
        body {
            background-image: url('votery_wall.jpeg');
            background-size: cover;
            background-position: center;
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #2b468c;
        }
        .login-container {
            background: #8fb9e1;
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        h1 {
            text-align: center;
            margin-bottom: 15px;
            color: #2b468c;
        }
        .login-container input {
            width: 94%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            color: #333;
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #00509e;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        .login-container button:hover {
            background-color: #00509E;
            transform: scale(1.05);
        }
        .login-container a {
            color: #2b468c;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome to Votery</h1>
        <p>Please log in to cast your votes or make votes </p>
        <!-- Tampilkan pesan error jika ada -->
        <?php if ($error): ?>
            <p style="color: red; text-align: center;"> <?= htmlspecialchars($error) ?> </p>
        <?php endif; ?>
        
        <!-- Form login -->
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="sign in.php">Don't have an account yet? Register here</a>
        <a href="home.php" class="button">Back to Dashboard</a>
    </div>
</body>
</html>
