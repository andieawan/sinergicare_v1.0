<?php
// logout.php (Root Directory)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bersihkan seluruh data array session aplikasi
$_SESSION = [];

// Hancurkan cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sisa metadata sesi server
session_destroy();

// Tendang pengguna kembali ke halaman gerbang masuk login
header("Location: /login.php");
exit();