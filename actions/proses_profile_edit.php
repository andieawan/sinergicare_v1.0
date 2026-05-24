<?php
// actions/proses_profile_edit.php

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /edit_profile.php");
    exit();
}

$user_id       = (int)$_SESSION['user_id'];
$nama          = trim($_POST['nama']          ?? '');
$email         = trim($_POST['email']         ?? '');
$password_lama = $_POST['password_lama']      ?? '';
$password_baru = $_POST['password_baru']      ?? '';
$password_conf = $_POST['password_confirm']   ?? '';

// Validasi input dasar
if (empty($nama)) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Nama tidak boleh kosong.'];
    header("Location: /edit_profile.php");
    exit();
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Format email tidak valid.'];
    header("Location: /edit_profile.php");
    exit();
}

if ($conn === null) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Koneksi database gagal.'];
    header("Location: /edit_profile.php");
    exit();
}

try {
    // Ambil data user saat ini untuk verifikasi password lama
    $stmt_get = $conn->prepare("SELECT id, password FROM staf_sekolah WHERE id = ? LIMIT 1");
    $stmt_get->execute([$user_id]);
    $user_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        $_SESSION['notif'] = ['type' => 'error', 'message' => 'Akun tidak ditemukan.'];
        header("Location: /edit_profile.php");
        exit();
    }

    // Verifikasi password lama (wajib diisi untuk semua perubahan)
    $password_valid = password_verify($password_lama, $user_data['password'])
                   || ($password_lama === $user_data['password']); // fallback plaintext legacy

    if (!$password_valid) {
        $_SESSION['notif'] = ['type' => 'error', 'message' => 'Password saat ini tidak cocok. Perubahan dibatalkan.'];
        header("Location: /edit_profile.php");
        exit();
    }

    // PERBAIKAN SEKURITAS: Validasi keunikan alamat email sebelum melakukan update data
    if (!empty($email)) {
        $stmt_email = $conn->prepare("SELECT id FROM staf_sekolah WHERE email = ? AND id != ? LIMIT 1");
        $stmt_email->execute([$email, $user_id]);
        if ($stmt_email->fetch()) {
            $_SESSION['notif'] = ['type' => 'error', 'message' => '⚠️ Gagal memperbarui profil! Alamat email tersebut sudah digunakan oleh akun staf lain.'];
            header("Location: /edit_profile.php");
            exit();
        }
    }

    // Validasi password baru jika diisi
    if (!empty($password_baru)) {
        if (strlen($password_baru) < 6) {
            $_SESSION['notif'] = ['type' => 'error', 'message' => 'Password baru minimal 6 karakter.'];
            header("Location: /edit_profile.php");
            exit();
        }
        if ($password_baru !== $password_conf) {
            $_SESSION['notif'] = ['type' => 'error', 'message' => 'Konfirmasi password baru tidak cocok.'];
            header("Location: /edit_profile.php");
            exit();
        }
    }

    if (!empty($password_baru)) {
        // Update profil + password
        $hashed = password_hash($password_baru, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE staf_sekolah SET nama = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $hashed, $user_id]);
    } else {
        // Update profil saja
        $stmt = $conn->prepare("UPDATE staf_sekolah SET nama = ?, email = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $user_id]);
    }

    // Perbarui nama di session agar topbar langsung terupdate
    $_SESSION['user_nama'] = $nama;

    $_SESSION['notif'] = ['type' => 'success', 'message' => 'Profil berhasil diperbarui.'];

} catch (PDOException $e) {
    $_SESSION['notif'] = ['type' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()];
}

header("Location: /edit_profile.php");
exit();