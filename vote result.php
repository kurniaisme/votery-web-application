<?php
session_start(); // Memulai sesi untuk melacak data pengguna yang login

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika pengguna belum login
    exit();
}

// Koneksi ke database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'votehub_webapp';

// Membuat koneksi ke database menggunakan MySQLi
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Jika gagal terkoneksi, tampilkan pesan kesalahan
}

// Ambil username pengguna dari sesi
$username = $_SESSION['username'] ?? ''; // Mendapatkan username dari sesi jika ada
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?"); // Query untuk mendapatkan ID pengguna
$stmt_user->bind_param("s", $username); // Mengikat parameter untuk username
$stmt_user->execute(); // Eksekusi query
$result_user = $stmt_user->get_result(); // Ambil hasil query
$user_data = $result_user->fetch_assoc(); // Ambil data pengguna dalam bentuk array asosiatif
$user_id = $user_data['id'] ?? 0; // Menyimpan ID pengguna
$stmt_user->close(); // Tutup statement setelah selesai

// Ambil ID voting dari URL
$vote_id = $_GET['vote_id'] ?? null; // Ambil nilai vote_id dari URL
if (!$vote_id) {
    die("Vote ID is invalid."); // Jika vote_id tidak ada, tampilkan pesan kesalahan
}

// Ambil informasi voting berdasarkan vote_id
$stmt_vote = $conn->prepare("SELECT title FROM votes WHERE id = ?"); // Query untuk mendapatkan judul voting
$stmt_vote->bind_param("i", $vote_id); // Mengikat parameter untuk vote_id
$stmt_vote->execute(); // Eksekusi query
$result_vote = $stmt_vote->get_result(); // Ambil hasil query
$vote_details = $result_vote->fetch_assoc(); // Ambil detail voting dalam bentuk array asosiatif
$stmt_vote->close(); // Tutup statement setelah selesai

if (!$vote_details) {
    die("Voting was not found."); // Jika voting tidak ditemukan, tampilkan pesan kesalahan
}

// Judul voting untuk ditampilkan
$vote_title = htmlspecialchars($vote_details['title']); // Mengambil dan membersihkan judul voting untuk menghindari XSS

// Mengatur redirect ke dashboard setelah beberapa detik
$redirect_url = "dashboard.php"; // URL yang dituju setelah redirect
$redirect_time = 5; // Waktu delay sebelum redirect (dalam detik)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $redirect_time ?>;url=<?= $redirect_url ?>"> <!-- Mengatur redirect otomatis -->
    <title>voting results</title>
    <style>
        /* Gaya CSS untuk tampilan halaman */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('votery_wall.jpeg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color:rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 500px;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container p {
            margin: 10px 0;
            color: #555;
        }
        
        .redirect-message {
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thank You!</h2> <!-- Menampilkan pesan terima kasih -->
        <p>You have successfully participated in voting "<strong><?= $vote_title ?></strong>".</p> <!-- Menampilkan judul voting -->
        <p class="redirect-message">You will be redirected back to the dashboard in <?= $redirect_time ?> second...</p> <!-- Pesan countdown -->
    </div>
</body>
</html>

