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
    die("Connection failed: " . $conn->connect_error);
}

// Ambil ID pengguna yang sedang login
$username = $_SESSION['username'] ?? '';
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$user_id = $user_data['id'] ?? 0;
$stmt_user->close();

// Ambil ID voting dari URL
$vote_id = $_GET['vote_id'] ?? null;
if (!$vote_id) {
    die("Vote ID is invalid.");
}

// Ambil detail voting
$stmt_vote = $conn->prepare("
    SELECT v.id, v.title, v.description, v.unique_code, v.created_at, u.username AS creator 
    FROM votes v
    JOIN users u ON v.creator_id = u.id
    WHERE v.id = ?
");
$stmt_vote->bind_param("i", $vote_id);
$stmt_vote->execute();
$result_vote = $stmt_vote->get_result();
$vote_details = $result_vote->fetch_assoc();
$stmt_vote->close();

if (!$vote_details) {
    die("Voting was not found.");
}

// Ambil opsi voting
$stmt_options = $conn->prepare("SELECT id, option_text FROM vote_options WHERE vote_id = ?");
$stmt_options->bind_param("i", $vote_id);
$stmt_options->execute();
$result_options = $stmt_options->get_result();
$options = [];
while ($row = $result_options->fetch_assoc()) {
    $options[] = $row;
}
$stmt_options->close();

// Proses pemilihan opsi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vote_option'])) {
    $option_id = $_POST['option_id'] ?? null;

    // Validasi opsi
    $stmt_check = $conn->prepare("SELECT id FROM vote_options WHERE id = ? AND vote_id = ?");
    $stmt_check->bind_param("ii", $option_id, $vote_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $valid_option = $result_check->num_rows > 0;
    $stmt_check->close();

    if ($valid_option) {
        // Tambahkan user ke tabel peserta jika belum ada
        $stmt_participant = $conn->prepare("SELECT id FROM vote_participants WHERE vote_id = ? AND user_id = ?");
        $stmt_participant->bind_param("ii", $vote_id, $user_id);
        $stmt_participant->execute();
        $result_participant = $stmt_participant->get_result();
        $participant_data = $result_participant->fetch_assoc();
        $participant_id = $participant_data['id'] ?? null;
        $stmt_participant->close();

        if (!$participant_id) {
            $stmt_insert_participant = $conn->prepare("INSERT INTO vote_participants (vote_id, user_id) VALUES (?, ?)");
            $stmt_insert_participant->bind_param("ii", $vote_id, $user_id);
            $stmt_insert_participant->execute();
            $participant_id = $stmt_insert_participant->insert_id;
            $stmt_insert_participant->close();
        }

        // Tambahkan hasil voting
        $stmt_vote_result = $conn->prepare("INSERT INTO vote_results (vote_id, participant_id, option_id) VALUES (?, ?, ?)");
        $stmt_vote_result->bind_param("iii", $vote_id, $participant_id, $option_id);
        $stmt_vote_result->execute();
        $stmt_vote_result->close();

        // Redirect ke halaman hasil
        header("Location: vote result.php?vote_id=" . $vote_id);
        exit();
    } else {
        $error = "The selected option is invalid.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Details</title>
    <style>
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
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .container .option-list {
            margin-bottom: 20px;
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
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= htmlspecialchars($vote_details['title']) ?></h2>
        <p><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($vote_details['description'])) ?></p>
        <p><strong>Dibuat oleh:</strong> <?= htmlspecialchars($vote_details['creator']) ?></p>
        <p><strong>Kode Unik:</strong> <?= htmlspecialchars($vote_details['unique_code']) ?></p>
        <hr>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form action="vote details.php?vote_id=<?= $vote_id ?>" method="POST">
            <div class="option-list">
                <?php foreach ($options as $option): ?>
                    <label>
                        <input type="radio" name="option_id" value="<?= $option['id'] ?>" required>
                        <?= htmlspecialchars($option['option_text']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="vote_option">Submit Vote</button>
        </form>
    </div>
</body>
</html>

