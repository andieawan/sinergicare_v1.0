<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roles    = trim($_POST['roles'] ?? 'guru'); // Standardisasi nama kolom: roles

    // 1. Validasi Kelengkapan Parameter Induk
    if ($id <= 0 || empty($nama) || empty($email) || empty($username)) {
        setFlash('error', '⚠️ Gagal! Data parameter pembaruan staf tidak lengkap.');
        header("Location: /pages/admin.php");
        exit();
    }

    // 2. PERBAIKAN SEKURITAS: Validasi Whitelist Peran (Roles) Otoritas Staf
    $allowed_roles = ['guru', 'bk', 'admin', 'super_admin', 'waka_kesiswaan', 'kepala_jurusan', 'yayasan'];
    if (!in_array($roles, $allowed_roles)) {
        setFlash('error', '⚠️ Gagal! Perubahan tingkat hak akses (role) ditolak karena tidak valid.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek keunikan username & email pada entitas ID staf yang lain
        $stmt_check = $conn->prepare("SELECT id FROM staf_sekolah WHERE (username = ? OR email = ?) AND id != ? LIMIT 1");
        $stmt_check->execute([$username, $email, $id]);
        if ($stmt_check->fetch()) {
            setFlash('error', '⚠️ Gagal! Username atau Email tersebut sudah dialokasikan untuk staf lain.');
            header("Location: /pages/admin.php");
            exit();
        }

        if (!empty($password)) {
            // Kasus A: User ingin memperbarui profil sekaligus merubah password lamanya
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt_update = $conn->prepare("UPDATE staf_sekolah SET nama = ?, email = ?, username = ?, password = ?, roles = ? WHERE id = ?");
            $stmt_update->execute([$nama, $email, $username, $password_hashed, $roles, $id]);
        } else {
            // Kasus B: User hanya memperbarui profil tanpa menyentuh password (password tetap)
            $stmt_update = $conn->prepare("UPDATE staf_sekolah SET nama = ?, email = ?, username = ?, roles = ? WHERE id = ?");
            $stmt_update->execute([$nama, $email, $username, $roles, $id]);
        }

        setFlash('success', '✨ Otoritas modifikasi data profil staf berhasil disimpan.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memperbarui data staf: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request tidak diizinkan!');
}

header("Location: /pages/admin.php");
exit();