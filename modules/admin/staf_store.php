<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roles    = trim($_POST['roles'] ?? 'guru'); // Standardisasi nama kolom: roles

    // 1. Validasi Kolom Kredensial Wajib
    if (empty($nama) || empty($email) || empty($username) || empty($password)) {
        setFlash('error', '⚠️ Gagal! Seluruh kredensial akun staf wajib dilengkapi.');
        header("Location: /pages/admin.php");
        exit();
    }

    // 2. PERBAIKAN SEKURITAS: Validasi Whitelist Peran (Roles) Otoritas Staf
    $allowed_roles = ['guru', 'bk', 'admin', 'super_admin', 'waka_kesiswaan', 'kepala_jurusan', 'yayasan'];
    if (!in_array($roles, $allowed_roles)) {
        setFlash('error', '⚠️ Gagal! Tingkat hak akses (role) staf tidak sah atau tidak dikenal sistem.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek keunikan username & email
        $stmt_check = $conn->prepare("SELECT id FROM staf_sekolah WHERE username = ? OR email = ? LIMIT 1");
        $stmt_check->execute([$username, $email]);
        if ($stmt_check->fetch()) {
            setFlash('error', '⚠️ Gagal! Username atau Email sudah terdaftar di sistem.');
            header("Location: /pages/admin.php");
            exit();
        }

        // Enkripsi kata sandi menggunakan standar BCRYPT
        $password_hashed = password_hash($password, PASSWORD_BCRYPT);

        // Masukkan akun staf baru
        $stmt_insert = $conn->prepare("
            INSERT INTO staf_sekolah (nama, email, username, password, roles, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt_insert->execute([$nama, $email, $username, $password_hashed, $roles]);

        setFlash('success', "✨ Akun otoritas staf baru atas nama '{$nama}' berhasil dibuat.");
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal membuat otorisasi akun staf: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request data tidak sah!');
}

header("Location: /pages/admin.php");
exit();