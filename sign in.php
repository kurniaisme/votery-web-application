<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Votery - Sign in</title>
  <style>
    /* Gaya CSS untuk halaman */
    body {
      font-family: Arial, sans-serif; /* Font yang digunakan di seluruh halaman */
      margin: 0; /* Menghapus margin default */
      padding: 0; /* Menghapus padding default */
      animation: fadeIn 1.5s ease-in-out; /* Animasi saat halaman dimuat */
      background-image: url('votery_wall.jpeg'); /* Gambar latar belakang halaman */
    }
    
    /* Animasi fadeIn */
    @keyframes fadeIn { 
      from { opacity: 0; } /* Mulai dari transparan */
      to { opacity: 1; } /* Berakhir dengan tampilan penuh */
    }

    /* Kontainer utama untuk formulir pendaftaran */
    .container {
      max-width: 800px; /* Lebar maksimum kontainer */
      margin: 50px auto; /* Margin atas dan bawah, tengah secara horizontal */
      padding: 20px; /* Padding di dalam kontainer */
      background-color:  #8fb9e1; /* Warna latar belakang kontainer */
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Bayangan untuk efek kedalaman */
      border-radius: 10px; /* Sudut melengkung */
      animation: slideIn 1s ease-in-out; /* Animasi saat kontainer muncul */
    }

    /* Animasi slideIn */
    @keyframes slideIn { 
      from { transform: translateY(20px); opacity: 0; } /* Mulai dari bawah dan transparan */
      to { transform: translateY(0); opacity: 1; } /* Berakhir di posisi normal dan terlihat */
    }

    /* Gaya untuk judul dan paragraf */
    h2, p { 
      text-align: center; /* Pusatkan teks */
      color: #2b468c; /* Warna teks */
    }

    /* Gaya untuk grup formulir */
    .form-group { 
      margin-bottom: 15px; /* Jarak antara grup formulir */
      color: #2b468c; /* Warna teks untuk label */
    }

    label { 
      display: block; /* Tampilkan label sebagai blok */
      margin-bottom: 5px; /* Jarak di bawah label */
    }

    /* Gaya untuk input dan select */
    input, select {
      width: 96%; /* Lebar penuh */
      padding: 10px; /* Padding di dalam input */
      border: 1px solid #2b468c; /* Border berwarna */
      border-radius: 5px; /* Sudut melengkung */
      background-color: #f9f9f9; /* Warna latar belakang input */
    }

    /* Gaya untuk tombol daftar */
    .btn-daftar {
      background-color:  #2b468c; /* Warna latar belakang tombol */
      color: #fff; /* Warna teks tombol */
      padding: 10px 20px; /* Padding dalam tombol */
      border: none; /* Tanpa border */
      border-radius: 5px; /* Sudut melengkung */
      cursor: pointer; /* Kursor tangan saat hover */
      transition: background-color 0.3s, transform 0.3s; /* Transisi halus untuk efek hover */
      text-align: center; /* Pusatkan teks */
      display: inline-block; /* Tampilkan sebagai blok inline */
    }

    /* Efek hover untuk tombol daftar */
    .btn-daftar:hover { 
      background-color: rgb(43, 68, 133); /* Warna saat hover */
      transform: scale(1.05); /* Efek zoom saat hover */
    }

    /* Gaya untuk tombol kembali */
    .btn-kembali {
      background-color: #6c757d; /* Warna latar belakang tombol */
      color: #fff; /* Warna teks tombol */
      padding: 8px 20px; /* Padding dalam tombol */
      border: none; /* Tanpa border */
      border-radius: 5px; /* Sudut melengkung */
      cursor: pointer; /* Kursor tangan saat hover */
      transition: background-color 0.3s, transform 0.3s; /* Transisi halus untuk efek hover */
      text-align: center; /* Pusatkan teks */
      display: inline-block; /* Tampilkan sebagai blok inline */
      text-decoration: none; /* Tanpa garis bawah */
    }

    /* Efek hover untuk tombol kembali */
    .btn-kembali:hover { 
      background-color: #5a6268; /* Warna saat hover */
      transform: scale(1.05); /* Efek zoom saat hover */
    }
  </style>
</head>
<body>
  <?php
  // Memeriksa apakah permintaan metode POST diterima
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Konfigurasi koneksi database
      $host = 'localhost'; // Alamat host database
      $user = 'root'; // Username database
      $password = ''; // Password database
      $database = 'votehub_webapp'; // Nama database

      // Membuat koneksi ke database
      $conn = mysqli_connect($host, $user, $password, $database);

      // Periksa koneksi
      if (!$conn) {
          die("<p style='color: red; text-align: center;'>Koneksi ke database gagal: " . mysqli_connect_error() . "</p>");
      }

      // Ambil data dari form dan lakukan sanitasi untuk menghindari injeksi SQL
      $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
      $username = mysqli_real_escape_string($conn, $_POST['username']);
      $password = $_POST['password'];
      $konfirmasi_password = $_POST['konfirmasi_password'];
      $email = mysqli_real_escape_string($conn, $_POST['email']);
      $nomor_telepon = mysqli_real_escape_string($conn, $_POST['nomor_telepon']); // Konsistensi variabel

      // Validasi input kosong
      if (empty($nama_lengkap) || empty($username) || empty($password) || empty($konfirmasi_password) || empty($email) || empty($nomor_telepon)) {
          echo "<p style='color: red; text-align: center;'>Semua kolom harus diisi!</p>"; // Tampilkan pesan jika ada kolom yang kosong
      } elseif ($password !== $konfirmasi_password) { // Validasi konfirmasi password
          echo "<p style='color: red; text-align: center;'>Password dan konfirmasi password tidak cocok.</p>"; // Tampilkan pesan jika password tidak cocok
      } else {
          // Hash password untuk keamanan
          $password_hashed = password_hash($password, PASSWORD_DEFAULT);

          // Query untuk menyimpan data ke tabel users
          $sql = "INSERT INTO users (nama_lengkap, username, password, email, nomor_telepon, created_at) 
                  VALUES ('$nama_lengkap', '$username', '$password_hashed', '$email', '$nomor_telepon', NOW())";

          // Eksekusi query dan periksa hasilnya
          if (mysqli_query($conn, $sql)) {
              echo "<p style='color: green; text-align: center;'>Pendaftaran berhasil!</p>"; // Tampilkan pesan sukses
          } else {
              echo "<p style='color: red; text-align: center;'>Gagal mendaftar: " . mysqli_error($conn) . "</p>"; // Tampilkan pesan gagal
          }
      }

      // Tutup koneksi
      mysqli_close($conn);
  }
  ?>
  <section id="register" class="register-section" style="padding: 30px;">
    <div class="container">
      <h2>Daftar Sekarang</h2>
      <p style="text-align: center;">Isi formulir di bawah ini untuk mendaftar</p>
      <form action="" method="POST"> <!-- Form untuk pendaftaran -->
        <div class="form-group">
          <label for="nama_lengkap">Nama Lengkap</label>
          <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" required>
        </div>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
        </div>
        <div class="form-group">
          <label for="konfirmasi_password">Konfirmasi Password</label>
          <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Konfirmasi password Anda" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
        </div>
        <div class="form-group">
          <label for="nomor_telepon">Nomor Telepon</label>
          <input type="tel" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon Anda" required>
        </div>
        <button type="submit" class="btn-daftar">Daftar</button> <!-- Tombol untuk mengirim formulir -->
        <a href="home.php" class="btn-kembali">Kembali</a> <!-- Tombol kembali ke halaman utama -->
      </form>
    </div>
  </section>
</body>
</html>
