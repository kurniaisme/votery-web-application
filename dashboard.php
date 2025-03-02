<?php
session_start(); // Memulai sesi untuk menyimpan informasi pengguna

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Jika belum login, redirect ke halaman login
    exit(); // Menghentikan eksekusi skrip
}

// Konfigurasi database
$host = 'localhost'; // Alamat host database
$user = 'root'; // Username database
$password = ''; // Password database
$database = 'votehub_webapp'; // Nama database

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error); // Jika koneksi gagal, tampilkan pesan error
}

// Ambil informasi pengguna dari sesi
$username = $_SESSION['username'] ?? ''; // Mengambil username dari sesi, jika tidak ada maka kosong

// Ambil ID pengguna dan nama lengkap dari database
$stmt_user = $conn->prepare("SELECT id, nama_lengkap FROM users WHERE username = ?");
$stmt_user->bind_param("s", $username); // Mengikat parameter untuk query
$stmt_user->execute(); // Menjalankan query
$result_user = $stmt_user->get_result(); // Mendapatkan hasil query
$user_data = $result_user->fetch_assoc(); // Mengambil data pengguna dalam bentuk array asosiasi
$user_id = $user_data['id'] ?? 0; // Mengambil ID pengguna, jika tidak ada maka 0
$nama_lengkap = $user_data['nama_lengkap'] ?? 'User'; // Mengambil nama lengkap pengguna, jika tidak ada maka 'User'
$stmt_user->close(); // Menutup pernyataan

// Jika user_id tidak valid, redirect ke halaman login
if (!$user_id) {
    session_unset(); // Menghapus semua variabel sesi
    session_destroy(); // Menghancurkan sesi
    header("Location: login.php"); // Redirect ke halaman login
    exit(); // Menghentikan eksekusi skrip
}

// Query untuk menghitung data terkait pengguna
$stmt_stats = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM votes WHERE creator_id = ?) AS total_votes_created,
        (SELECT COUNT(DISTINCT vote_id) FROM vote_participants WHERE user_id = ?) AS total_votes_joined,
        (SELECT COUNT(DISTINCT vp.user_id)
         FROM vote_participants vp
         INNER JOIN votes v ON vp.vote_id = v.id
         WHERE v.creator_id = ?) AS total_participants
");
$stmt_stats->bind_param("iii", $user_id, $user_id, $user_id); // Mengikat parameter untuk query
$stmt_stats->execute(); // Menjalankan query
$result_stats = $stmt_stats->get_result(); // Mendapatkan hasil query
$stats = $result_stats->fetch_assoc(); // Mengambil data statistik dalam bentuk array asosiasi
$stmt_stats->close(); // Menutup pernyataan

// Mengambil jumlah suara yang dibuat dan bergabung
$total_votes_created = $stats['total_votes_created'] ?? 0; // Total suara yang dibuat
$total_votes_joined = $stats['total_votes_joined'] ?? 0; // Total suara yang diikuti
$total_participants = $stats['total_participants'] ?? 0; // Total peserta

// Hitung total stars (bintang) dari votes_created + votes_joined
$total_stars = $total_votes_created + $total_votes_joined; // Menghitung total bintang
$score = $total_stars * 2; // Menghitung skor berdasarkan total bintang

// Proses logout
if (isset($_GET['logout'])) {
    session_unset(); // Menghapus semua variabel sesi
    session_destroy(); // Menghancurkan sesi
    header("Location: home.php"); // Redirect ke halaman utama
    exit(); // Menghentikan eksekusi skrip
}
?>
<!DOCTYPE html>
<html lang="id"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votery - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Mengimpor library Chart.js untuk grafik -->
    <style>
        /* Gaya untuk tubuh halaman */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('votery_wall.jpeg'); /* Gambar latar belakang */
            background-size: cover;
            background-position: center;
        }

        /* Kontainer dashboard */
        .dashboard-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.77); /* Warna latar belakang kontainer */
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.38); /* Bayangan untuk efek kedalaman */
            padding: 20px;
            width: 80%; /* Lebar kontainer */
        }

        /* Seksi sambutan */
        .welcome-section {
            flex: 1;
            padding: 20px; /* Padding dalam seksi sambutan */
        }

        /* Gaya untuk judul dan paragraf di seksi sambutan */
        .welcome-section h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .welcome-section p {
            margin: 10px 0 20px;
            color: #555;
        }

        /* Gaya untuk tombol di seksi sambutan */
        .welcome-section button {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            border-radius: 4px; /* Sudut melengkung */
            cursor: pointer; /* Kursor tangan saat hover */
            background-color: #00509e; /* Warna latar belakang tombol */
            color: #fff; /* Warna teks tombol */
            font-size: 14px;
        }

        /* Efek hover untuk tombol */
        .welcome-section button:hover {
            background-color: #333; /* Warna saat hover */
        }

        /* Kartu skor */
        .score-card {
            flex: 1; /* Mengambil ruang yang tersedia */
            background-color: #f8f9fa; /* Warna latar belakang kartu */
            border-radius: 8px; /* Sudut melengkung */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Bayangan untuk efek kedalaman */
            padding: 20px; /* Padding dalam kartu */
            text-align: center; /* Pusatkan teks */
        }

        /* Gaya untuk judul dan skor dalam kartu */
        .score-card h3 {
            margin: 0;
            font-size: 18px;
            color: #00509e; /* Warna teks judul */
        }

        .score-card .score {
            font-size: 48px; /* Ukuran teks skor */
            font-weight: bold; /* Ketebalan teks */
            margin: 10px 0; /* Jarak atas dan bawah */
            color: #333; /* Warna teks skor */
        }

        /* Gaya untuk detail skor */
        .score-card .score-details {
            display: grid; /* Menampilkan dalam bentuk grid */
            grid-template-columns: repeat(2, 1fr); /* Dua kolom */
            gap: 10px; /* Jarak antar elemen */
            margin-top: 20px; /* Jarak atas */
        }

        .score-card .score-details div {
            background-color: #00509e; /* Warna latar belakang detail skor */
            border-radius: 4px; /* Sudut melengkung */
            padding: 10px; /* Padding dalam detail skor */
            text-align: center; /* Pusatkan teks */
        }

        /* Gaya untuk teks dalam detail skor */
        .score-card .score-details div span {
            display: block; /* Tampilkan sebagai blok */
            font-size: 14px; /* Ukuran teks */
            color: rgb(255, 255, 255); /* Warna teks */
        }

        .score-card .score-details div .value {
            font-size: 18px; /* Ukuran teks nilai */
            font-weight: bold; /* Ketebalan teks */
            color: rgb(255, 255, 255); /* Warna teks */
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-section">
            <h2>Hi, <?= htmlspecialchars($nama_lengkap); ?>!</h2> <!-- Menampilkan nama lengkap pengguna -->
            <p>Welcome to your Votery dashboard. This is your hub to manage your votes and track your progress. We hope you enjoy using Votery!</p>
            <a href="join vote.php"> <!-- Link untuk bergabung dalam voting -->
                <button>Join Vote</button>
            </a>
            <a href="create vote.php"> <!-- Link untuk membuat voting -->
                <button>Create Vote</button>
            </a>
            <a href="see result.php"> <!-- Link untuk melihat hasil voting -->
                <button>See Results</button>
            </a>
            <a href="?logout=true"> <!-- Link untuk logout -->
                <button>Logout</button>
            </a>
        </div>

        <div class="score-card">
            <h3>Good job!</h3> <!-- Pesan selamat -->
            <div class="score"><?= $score ?></div> <!-- Menampilkan skor pengguna -->
            <p>Your score</p> <!-- Teks penjelas -->
            <div class="score-details"> <!-- Detail skor -->
                <div>
                    <span>Votes Created</span> <!-- Label untuk suara yang dibuat -->
                    <div class="value"><?= $total_votes_created ?></div> <!-- Menampilkan jumlah suara yang dibuat -->
                </div>
                <div>
                    <span>Participants</span> <!-- Label untuk peserta -->
                    <div class="value"><?= $total_participants ?></div> <!-- Menampilkan jumlah peserta -->
                </div>
                <div>
                    <span>Votes Joined</span> <!-- Label untuk suara yang diikuti -->
                    <div class="value"><?= $total_votes_joined ?></div> <!-- Menampilkan jumlah suara yang diikuti -->
                </div>
                <div>
                    <span>Stars Collected</span> <!-- Label untuk bintang yang dikumpulkan -->
                    <div class="value"><?= $total_stars ?></div> <!-- Menampilkan jumlah bintang yang dikumpulkan -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>
