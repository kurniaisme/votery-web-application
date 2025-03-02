<?php
session_start(); // Memulai sesi untuk menyimpan informasi pengguna

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Jika belum login, redirect ke halaman login
    exit(); // Menghentikan eksekusi skrip
}

// Koneksi ke database
$host = 'localhost'; // Alamat host database
$user = 'root'; // Username database
$password = ''; // Password database
$database = 'votehub_webapp'; // Nama database

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error); // Jika koneksi gagal, tampilkan pesan error
}

// Ambil ID pengguna yang sedang login
$username = $_SESSION['username'] ?? ''; // Mengambil username dari sesi
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?"); // Menyiapkan query untuk mengambil ID pengguna
$stmt_user->bind_param("s", $username); // Mengikat parameter untuk query
$stmt_user->execute(); // Menjalankan query
$result_user = $stmt_user->get_result(); // Mendapatkan hasil query
$user_data = $result_user->fetch_assoc(); // Mengambil data pengguna dalam bentuk array asosiasi
$user_id = $user_data['id'] ?? 0; // Mengambil ID pengguna, jika tidak ada maka 0
$stmt_user->close(); // Menutup pernyataan

// Ambil kode unik dari URL
$unique_code = $_GET['vote_code'] ?? ''; // Mengambil kode unik dari URL jika ada

// Ambil data vote berdasarkan kode unik
$stmt_vote = $conn->prepare("SELECT * FROM votes WHERE unique_code = ? AND creator_id = ?"); // Menyiapkan query untuk mengambil data vote
$stmt_vote->bind_param("si", $unique_code, $user_id); // Mengikat parameter untuk query
$stmt_vote->execute(); // Menjalankan query
$result_vote = $stmt_vote->get_result(); // Mendapatkan hasil query

if ($result_vote->num_rows > 0) { // Jika ada data vote
    $vote_data = $result_vote->fetch_assoc(); // Mengambil data vote dalam bentuk array asosiasi
    $title = $vote_data['title']; // Mengambil judul vote
    $description = $vote_data['description']; // Mengambil deskripsi vote
    $unique_code = $vote_data['unique_code']; // Mengambil kode unik vote
} else {
    die("Vote not found or you do not have access to this vote."); // Jika tidak ada vote, tampilkan pesan error
}
$stmt_vote->close(); // Menutup pernyataan
?>

<!DOCTYPE html>
<html lang="id"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Created Vote</title>
    <style>
        body {
            font-family: Arial, sans-serif; /* Font untuk halaman */
            margin: 0;
            padding: 0;
            background-image: url('votery_wall.jpeg'); /* Gambar latar belakang */
            background-size: cover; /* Ukuran gambar latar belakang */
            background-position: center; /* Posisi gambar latar belakang */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Tinggi halaman penuh */
        }

        .container {
            background-color:rgba(255, 255, 255, 0.8); /* Warna latar belakang kontainer */
            padding: 30px; /* Padding dalam kontainer */
            border-radius: 8px; /* Sudut melengkung */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Bayangan untuk efek kedalaman */
            width: 400px; /* Lebar kontainer */
        }

        .container h2 {
            text-align: center; /* Pusatkan teks judul */
            margin-bottom: 20px; /* Jarak bawah judul */
        }

        .container p {
            font-size: 16px; /* Ukuran font untuk paragraf */
            line-height: 1.5; /* Jarak antar baris dalam paragraf */
            margin-bottom: 20px; /* Jarak bawah paragraf */
            text-align: center; /* Pusatkan teks dalam paragraf */
        }

        .container .code-box {
            background-color: #f1f1f1; /* Warna latar belakang kotak kode */
            padding: 10px; /* Padding dalam kotak kode */
            border-radius: 4px; /* Sudut melengkung kotak kode */
            text-align: center; /* Pusatkan teks dalam kotak kode */
            font-weight: bold; /* Cetak tebal untuk teks dalam kotak kode */
            font-size: 18px; /* Ukuran font untuk teks dalam kotak kode */
            margin-bottom: 20px; /* Jarak bawah kotak kode */
        }

        .container button {
            width: 100%; /* Lebar tombol */
            padding: 10px; /* Padding dalam tombol */
            background-color:rgb(15, 171, 48); /* Warna latar belakang tombol */
            color: white; /* Warna teks tombol */
            border: none; /* Tanpa border */
            border-radius: 4px; /* Sudut melengkung */
            cursor: pointer; /* Kursor tangan saat hover */
            font-size: 16px; /* Ukuran font tombol */
        }

        .container button:hover {
            background-color: rgb(8, 125, 33); /* Warna saat hover untuk tombol */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Vote Created!</h2> <!-- Judul halaman -->

        <p><?= htmlspecialchars($title) ?></p> <!-- Menampilkan judul vote -->
        <p><?= htmlspecialchars($description) ?></p> <!-- Menampilkan deskripsi vote -->

        <div class="code-box">
            <p>Here's your voting code:</p> <!-- Teks untuk kode voting -->
            <p><strong><?= htmlspecialchars($unique_code) ?></strong></p> <!-- Menampilkan kode unik vote -->
        </div>

        <p>You can share this code with other users who can join your vote.</p> <!-- Pesan untuk membagikan kode -->

        <a href="dashboard.php"><button>Back to Dashboard</button></a> <!-- Tombol untuk kembali ke dashboard -->
    </div>
</body>
</html>
