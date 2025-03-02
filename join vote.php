<?php
// Mulai sesi pengguna
session_start();

// Validasi sesi pengguna: cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    // Jika tidak login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'votehub_webapp';

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan kesalahan
    die("Connection failed: " . $conn->connect_error);
}

// Ambil ID pengguna yang sedang login berdasarkan username
$username = $_SESSION['username'] ?? '';
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$user_id = $user_data['id'] ?? 0;
$stmt_user->close();

// Proses ketika pengguna mengirimkan form untuk join vote
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_vote'])) {
    // Ambil kode unik yang dimasukkan pengguna
    $unique_code = $_POST['unique_code'] ?? '';

    // Validasi kode unik: cek apakah kode ada di tabel votes
    $stmt_vote = $conn->prepare("SELECT id FROM votes WHERE unique_code = ?");
    $stmt_vote->bind_param("s", $unique_code);
    $stmt_vote->execute();
    $result_vote = $stmt_vote->get_result();
    $vote_data = $result_vote->fetch_assoc();
    $vote_id = $vote_data['id'] ?? null;
    $stmt_vote->close();

    if ($vote_id) {
        // Cek apakah pengguna sudah join vote ini sebelumnya
        $stmt_check = $conn->prepare("SELECT id FROM vote_participants WHERE vote_id = ? AND user_id = ?");
        $stmt_check->bind_param("ii", $vote_id, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Jika sudah join, tampilkan pesan kesalahan
            $error = "You've already joined this vote.";
        } else {
            // Jika belum join, tambahkan ke tabel vote_participants
            $stmt_participant = $conn->prepare("INSERT INTO vote_participants (vote_id, user_id) VALUES (?, ?)");
            $stmt_participant->bind_param("ii", $vote_id, $user_id);
            $stmt_participant->execute();
            $stmt_participant->close();

            // Setelah berhasil, arahkan ke halaman detail vote
            header("Location: vote details.php?vote_id=" . $vote_id);
            exit();
        }
    } else {
        // Jika kode unik tidak valid, tampilkan pesan kesalahan
        $error = "Invalid unique code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votery - Join Vote</title>
    <style>
        /* Gaya halaman */
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
            width: 400px;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .container input {
            width: 94%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .container button {
            width: 100%;
            padding: 10px;
            background-color: #00509e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .container button:hover {
            background-color: #333;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Join Vote</h2>
        <!-- Tampilkan pesan error jika ada -->
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <!-- Form untuk join vote -->
        <form action="join vote.php" method="POST">
            <label for="unique_code">Enter Unique Code:</label>
            <input type="text" id="unique_code" name="unique_code" required placeholder="Enter unique vote code">
            <button type="submit" name="join_vote">Join Vote</button>
        </form>
    </div>
</body>
</html>

