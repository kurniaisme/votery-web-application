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
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil vote_id dari URL
$vote_id = $_GET['vote_id'] ?? 0;

// Ambil data voting berdasarkan vote_id
$stmt_vote = $conn->prepare("SELECT title, description FROM votes WHERE id = ?");
$stmt_vote->bind_param("i", $vote_id);
$stmt_vote->execute();
$result_vote = $stmt_vote->get_result();
$vote_data = $result_vote->fetch_assoc();
$stmt_vote->close();

// Jika data voting tidak ditemukan, kembalikan ke halaman sebelumnya
if (!$vote_data) {
    header("Location: see result.php");
    exit();
}

// Ambil daftar peserta dan pilihan mereka
$query_participants = "
    SELECT 
        users.username,
        users.nama_lengkap,
        vote_options.option_text,
        vote_results.voted_at
    FROM 
        vote_results
    INNER JOIN vote_participants ON vote_results.participant_id = vote_participants.id
    INNER JOIN users ON vote_participants.user_id = users.id
    INNER JOIN vote_options ON vote_results.option_id = vote_options.id
    WHERE 
        vote_results.vote_id = ?
    ORDER BY 
        vote_results.voted_at ASC
";
$stmt_participants = $conn->prepare($query_participants);
$stmt_participants->bind_param("i", $vote_id);
$stmt_participants->execute();
$result_participants = $stmt_participants->get_result();
$participants = $result_participants->fetch_all(MYSQLI_ASSOC);
$stmt_participants->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votery - Result Details</title>
    <style>
        /* Gaya umum halaman */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('votery_wall.jpeg');
            background-size: cover;
            background-position: center;
        }

        /* Container utama */
        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Judul dan subjudul */
        h1, h2 {
            text-align: center;
            color: #333;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        /* Link kembali */
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Tombol kembali */
        .btn-container {
            text-align: center;
            margin-top: 20px;
            width: 10%;
        }

        .btn-dashboard {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-dashboard:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Judul dan deskripsi voting -->
        <h1>Result Details</h1>
        <h2><?= htmlspecialchars($vote_data['title']) ?></h2>
        <p>Description <?= htmlspecialchars($vote_data['description']) ?></p>

        <!-- Tabel peserta dan hasil voting -->
        <?php if (count($participants) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Options</th>
                        <th>Voting Times</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($participant['username']) ?></td>
                            <td><?= htmlspecialchars($participant['option_text']) ?></td>
                            <td><?= htmlspecialchars($participant['voted_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <!-- Pesan jika tidak ada peserta -->
            <p>There were no participants who took part in this vote.</p>
        <?php endif; ?>

        <!-- Tombol kembali -->
        <div class="btn-container">
            <a class="btn-dashboard" href="see result.php">Back</a>
        </div>
    </div>
</body>
</html>

