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

// Proses pembuatan vote
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_vote'])) {
    $title = $_POST['title'] ?? ''; // Mengambil judul vote dari input
    $description = $_POST['description'] ?? ''; // Mengambil deskripsi vote dari input
    $options = $_POST['options'] ?? []; // Mengambil opsi vote dari input
    
    // Cek jika judul dan opsi valid
    if (empty($title) || empty($options)) {
        $error = "The title and voting options cannot be empty."; // Pesan error jika input tidak valid
    } else {
        // Generate kode unik untuk vote
        $unique_code = strtoupper(substr(sha1(uniqid(rand(), true)), 0, 10)); // Membuat kode unik
        
        // Masukkan data vote ke tabel votes
        $stmt_vote = $conn->prepare("INSERT INTO votes (creator_id, title, description, unique_code) VALUES (?, ?, ?, ?)"); // Menyiapkan query untuk memasukkan data vote
        $stmt_vote->bind_param("isss", $user_id, $title, $description, $unique_code); // Mengikat parameter untuk query
        $stmt_vote->execute(); // Menjalankan query
        $vote_id = $stmt_vote->insert_id; // Mendapatkan ID vote yang baru dibuat
        $stmt_vote->close(); // Menutup pernyataan
        
        // Masukkan opsi voting ke tabel vote_options
        foreach ($options as $option) {
            $stmt_option = $conn->prepare("INSERT INTO vote_options (vote_id, option_text, created_by) VALUES (?, ?, ?)"); // Menyiapkan query untuk memasukkan opsi vote
            $stmt_option->bind_param("isi", $vote_id, $option, $user_id); // Mengikat parameter untuk query
            $stmt_option->execute(); // Menjalankan query
            $stmt_option->close(); // Menutup pernyataan
        }

        // Redirect ke halaman hasil pembuatan vote dengan kode unik
        header("Location: created%20vote.php?vote_code=" . $unique_code); // Redirect ke halaman hasil dengan kode unik
        exit(); // Menghentikan eksekusi skrip
    }
}
?>

<!DOCTYPE html>
<html lang="id"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votery - Create Vote</title>
    <style>
        body {
            font-family: Arial, sans-serif; /* Font untuk halaman */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Tinggi halaman penuh */
            background-image: url('votery_wall.jpeg'); /* Gambar latar belakang */
            background-size: cover; /* Ukuran gambar latar belakang */
            background-position: center; /* Posisi gambar latar belakang */
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

        .container input, .container textarea {
            width: 94%; /* Lebar input dan textarea */
            padding: 10px; /* Padding dalam input dan textarea */
            margin-bottom: 10px; /* Jarak bawah input dan textarea */
            border-radius: 4px; /* Sudut melengkung */
            border: 1px solid #ccc; /* Border input dan textarea */
            font-size: 14px; /* Ukuran font */
        }

        .container button {
            width: 100%; /* Lebar tombol */
            padding: 10px; /* Padding dalam tombol */
            background-color: #00509e; /* Warna latar belakang tombol */
            color: white; /* Warna teks tombol */
            border: none; /* Tanpa border */
            border-radius: 4px; /* Sudut melengkung */
            cursor: pointer; /* Kursor tangan saat hover */
            font-size: 16px; /* Ukuran font tombol */
        }

        .container button:hover {
            background-color: #333; /* Warna saat hover */
        }

        .container .option-list {
            margin-bottom: 10px; /* Jarak bawah daftar opsi */
        }

        .container .option-list input {
            margin-right: 10px; /* Jarak kanan input opsi */
        }

        .container button#add-option {
            margin-bottom: 20px; /* Jarak bawah tombol Add Option */
        }

        .btn-container {
            text-align: center; /* Pusatkan teks dalam kontainer tombol */
            margin-top: 20px; /* Jarak atas kontainer tombol */
        }

        .btn-dashboard {
            display: inline-block; /* Menampilkan tombol sebagai blok inline */
            background-color:rgb(15, 171, 48); /* Warna latar belakang tombol kembali */
            color: #fff; /* Warna teks tombol kembali */
            padding: 10px 20px; /* Padding dalam tombol kembali */
            border: none; /* Tanpa border */
            border-radius: 4px; /* Sudut melengkung */
            text-decoration: none; /* Tanpa garis bawah */
            font-size: 16px; /* Ukuran font tombol kembali */
            cursor: pointer; /* Kursor tangan saat hover */
        }

        .btn-dashboard:hover {
            background-color:rgb(8, 125, 33); /* Warna saat hover untuk tombol kembali */
        }

        .error {
            color: red; /* Warna teks untuk pesan error */
            font-size: 14px; /* Ukuran font pesan error */
            margin-bottom: 10px; /* Jarak bawah pesan error */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Your Vote</h2> <!-- Judul halaman -->
        <?php if (isset($error)): ?> <!-- Jika ada pesan error, tampilkan -->
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form action="create vote.php" method="POST"> <!-- Form untuk membuat vote -->
            <label for="title">Vote Title</label> <!-- Label untuk judul vote -->
            <input type="text" id="title" name="title" required placeholder="Enter vote title"> <!-- Input untuk judul vote -->

            <label for="description">Vote Description</label> <!-- Label untuk deskripsi vote -->
            <textarea id="description" name="description" rows="4" required placeholder="Enter vote description"></textarea> <!-- Textarea untuk deskripsi vote -->

            <label for="options">Vote Options</label> <!-- Label untuk opsi vote -->
            <div class="option-list" id="option-list">
                <input type="text" name="options[]" placeholder="Enter option" required> <!-- Input untuk opsi vote -->
            </div>
            <button type="button" id="add-option">Add Another Option</button> <!-- Tombol untuk menambahkan opsi -->
            <button type="submit" name="create_vote">Create Vote</button> <!-- Tombol untuk mengirim form dan membuat vote -->
            <div class="btn-container">
                <a class="btn-dashboard" href="dashboard.php">Back to Dashboard</a> <!-- Tombol untuk kembali ke dashboard -->
            </div>
        </form>
    </div>

    <script>
        // Menambahkan opsi baru
        document.getElementById('add-option').addEventListener('click', function() {
            var optionList = document.getElementById('option-list'); // Mengambil daftar opsi
            var input = document.createElement('input'); // Membuat elemen input baru
            input.type = 'text'; // Menetapkan tipe input
            input.name = 'options[]'; // Menetapkan nama input agar menjadi array
            input.placeholder = 'Enter option'; // Placeholder untuk input
            optionList.appendChild(input); // Menambahkan input ke daftar opsi
        });
    </script>
</body>
</html>
