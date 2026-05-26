<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/flash.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        setFlash('error', '⚠️ Username dan Password wajib diisi!');
        header("Location: /login.php");
        exit();
    }

    try {
        // Mencari kredensial staf berdasarkan username unik
        $stmt = $conn->prepare("SELECT * FROM staf_sekolah WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $password_valid = false;
            
            // Verifikasi password hash modern (tanpa fallback plaintext)
            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            }

            if ($password_valid) {
                // Mitigasi session fixation
                session_regenerate_id(true);

                // Registrasi data identitas ke dalam Session aplikasi
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_nama']  = $user['nama'];
                $_SESSION['user_roles'] = [$user['roles']]; // Dibungkus array sesuai standar core/auth.php

                // Redireksi langsung ke halaman Dashboard utama SinergiCare
                header("Location: /pages/dashboard.php");
                exit();
            } else {
                setFlash('error', '⚠️ Username atau kata sandi tidak valid.');
            }
        } else {
            setFlash('error', '⚠️ Username tidak terdaftar di sistem!');
        }
    } catch (PDOException $e) {
        error_log('Login DB error: ' . $e->getMessage());
        setFlash('error', '⚠️ Terjadi gangguan sistem. Silakan coba lagi.');
    }
} else {
    setFlash('error', '⚠️ Metode request data tidak sah!');
}

// Kembali ke halaman utama login jika otentikasi gagal
header("Location: /login.php");
exit();