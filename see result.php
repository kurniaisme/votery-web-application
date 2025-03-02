<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'votehub_webapp';

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Ambil username pengguna dari sesi
$username = $_SESSION['username'] ?? '';
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$user_id = $user_data['id'] ?? 0;
$stmt_user->close();

// Ambil daftar voting yang dibuat oleh pengguna
$stmt_votes = $conn->prepare("SELECT id, title, description, unique_code, created_at FROM votes WHERE creator_id = ?");
$stmt_votes->bind_param("i", $user_id);
$stmt_votes->execute();
$result_votes = $stmt_votes->get_result();
$votes = $result_votes->fetch_all(MYSQLI_ASSOC);
$stmt_votes->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Voting</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('votery_wall.jpeg');
            background-size: cover;
            background-position: center;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color:rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn-dashboard {
            display: inline-block;
            background-color: #00509e;
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

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid rgb(70, 70, 71);
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color:rgba(0, 79, 158, 0.6);
        }

        .details-link {
            color: #00509e;
            text-decoration: none;
        }

        .details-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Voting Histories</h1>

        <?php if (count($votes) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Voting Titles</th>
                        <th>Description</th>
                        <th>Unique Codes</th>
                        <th>Participants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($votes as $vote): ?>
                        <?php
                        // Hitung jumlah peserta untuk setiap voting
                        $stmt_participants = $conn->prepare("SELECT COUNT(*) AS participant_count FROM vote_participants WHERE vote_id = ?");
                        $stmt_participants->bind_param("i", $vote['id']);
                        $stmt_participants->execute();
                        $result_participants = $stmt_participants->get_result();
                        $participant_data = $result_participants->fetch_assoc();
                        $participant_count = $participant_data['participant_count'] ?? 0;
                        $stmt_participants->close();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($vote['title']) ?></td>
                            <td><?= htmlspecialchars($vote['description']) ?></td>
                            <td><?= htmlspecialchars($vote['unique_code']) ?></td>
                            <td><?= $participant_count ?></td>
                            <td>
                                <a class="details-link" href="result details.php?vote_id=<?= $vote['id'] ?>">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>There is no vote that you made.</p>
        <?php endif; ?>
        <!-- Tombol Kembali ke Dashboard -->
        <div class="btn-container">
            <a class="btn-dashboard" href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

