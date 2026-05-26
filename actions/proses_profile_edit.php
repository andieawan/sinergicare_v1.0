<?php
// actions/proses_profile_edit.php

// 1. Muat file konfigurasi database utama
require_once __DIR__ . '/../config/config.php';

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. PROTEKSI KEAMANAN: Tolak akses jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// 3. PROTEKSI KEAMANAN: Pastikan request dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /edit_profile.php");
    exit();
}

// Tangkap payload data dari formulir profil
$user_id       = (int)$_SESSION['user_id'];
$nama          = trim($_POST['nama']          ?? '');
$email         = trim($_POST['email']         ?? '');
$password_lama = $_POST['password_lama']      ?? '';
$password_baru = $_POST['password_baru']      ?? '';
$password_conf = $_POST['password_confirm']   ?? '';

// ============================================================================
// VALIDASI FORMULIR INPUT
// ============================================================================

// Validasi Nama Wajib Diisi
if (empty($nama)) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Nama lengkap wajib diisi dan tidak boleh kosong.'];
    header("Location: /edit_profile.php");
    exit();
}

// Validasi Format Struktur Email
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Format penulisan alamat email tidak valid.'];
    header("Location: /edit_profile.php");
    exit();
}

// Pastikan koneksi database PDO ($conn) tersedia dengan baik
if ($conn === null) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Koneksi ke server basis data gagal terhubung.'];
    header("Location: /edit_profile.php");
    exit();
}

try {
    // 1. Ambil hash password saat ini untuk memverifikasi keaslian user (Re-authentication)
    $stmt_get = $conn->prepare("SELECT id, password FROM staf_sekolah WHERE id = ? LIMIT 1");
    $stmt_get->execute([$user_id]);
    $user_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        $_SESSION['notif'] = ['type' => 'error', 'message' => 'Kredensial akun staf tidak dijumpai di sistem.'];
        header("Location: /edit_profile.php");
        exit();
    }

    // Verifikasi kecocokan password lama (Mendukung hash BCRYPT & fallback plaintext legacy)
    $password_valid = password_verify($password_lama, $user_data['password'])
                   || ($password_lama === $user_data['password']); 

    if (!$password_valid) {
        $_SESSION['notif'] = ['type' => 'error', 'message' => 'Password saat ini tidak cocok! Konfirmasi identitas gagal.'];
        header("Location: /edit_profile.php");
        exit();
    }

    // 2. VALIDASI UNIKSITAS EMAIL: Cegah penggunaan email yang sudah terdaftar di akun staf lain
    if (!empty($email)) {
        $stmt_email = $conn->prepare("SELECT id FROM staf_sekolah WHERE email = ? AND id != ? LIMIT 1");
        $stmt_email->execute([$email, $user_id]);
        if ($stmt_email->fetch()) {
            $_SESSION['notif'] = ['type' => 'error', 'message' => '⚠️ Gagal memperbarui! Alamat email tersebut sudah dialokasikan untuk staf lain.'];
            header("Location: /edit_profile.php");
            exit();
        }
    }

    // 3. VALIDASI KATA SANDI BARU (Jika diisi oleh pengguna)
    if (!empty($password_baru)) {
        if (strlen($password_baru) < 6) {
            $_SESSION['notif'] = ['type' => 'error', 'message' => 'Kata sandi baru minimal harus terdiri dari 6 karakter.'];
            header("Location: /edit_profile.php");
            exit();
        }
        if ($password_baru !== $password_conf) {
            $_SESSION['notif'] = ['type' => 'error', 'message' => 'Konfirmasi password baru tidak sesuai / tidak cocok.'];
            header("Location: /edit_profile.php");
            exit();
        }
    }

    // ============================================================================
    // EKSEKUSI PEMBARUAN DATA (UPDATE SQL)
    // ============================================================================

    if (!empty($password_baru)) {
        // Opsi A: Memperbarui informasi profil beserta kata sandi baru (Hashed via BCRYPT)
        $hashed = password_hash($password_baru, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE staf_sekolah SET nama = ?, email = ?, password = ?, must_change_password = 0 WHERE id = ?");
        $stmt->execute([$nama, $email, $hashed, $user_id]);
    } else {
        // Opsi B: Hanya memperbarui nama dan email saja (Password lama dipertahankan)
        $stmt = $conn->prepare("UPDATE staf_sekolah SET nama = ?, email = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $user_id]);
    }

    // Sinkronisasikan nama baru ke variabel sesi agar komponen Topbar/Sidebar langsung terupdate otomatis
    $_SESSION['user_nama'] = $nama;

    $_SESSION['notif'] = ['type' => 'success', 'message' => '✨ Selamat! Profil data diri Anda berhasil diperbarui.'];

} catch (PDOException $e) {
    // Tangkap kegagalan runtime driver database secara anggun
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Gagal menyimpan perubahan: ' . $e->getMessage()];
}

// Kembalikan pengguna ke halaman edit profile untuk melihat status notifikasi
header("Location: /edit_profile.php");
exit();