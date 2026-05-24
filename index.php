<?php
// index.php (Letakkan di FOLDER UTAMA / ROOT proyek Anda)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika pengguna sudah login, lempar ke dashboard baru
if (isset($_SESSION['user_id'])) {
    header("Location: /pages/dashboard.php");
} else {
    // Jika belum login, lempar ke halaman login
    header("Location: /login.php");
}
exit();