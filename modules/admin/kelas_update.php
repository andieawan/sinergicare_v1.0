<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $id         = (int)($_POST['id'] ?? 0);
    $nama_kelas = trim($_POST['nama_kelas'] ?? '');

    if ($id <= 0 || empty($nama_kelas)) {
        setFlash('error', '⚠️ Gagal! Parameter perubahan data kelas tidak lengkap.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek duplikasi nama kelas pada entitas ID yang berbeda
        $stmt_check = $conn->prepare("SELECT id FROM classes WHERE nama_kelas = ? AND id != ? LIMIT 1");
        $stmt_check->execute([$nama_kelas, $id]);
        if ($stmt_check->fetch()) {
            setFlash('error', "⚠️ Gagal! Nama kelas '{$nama_kelas}' sudah digunakan oleh kelas lain.");
            header("Location: /pages/admin.php");
            exit();
        }

        // Jalankan update
        $stmt_update = $conn->prepare("UPDATE classes SET nama_kelas = ? WHERE id = ?");
        $stmt_update->execute([$nama_kelas, $id]);

        setFlash('success', '✨ Berhasil memperbarui data nama kelas.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memperbarui data kelas: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request tidak diizinkan!');
}

header("Location: /pages/admin.php");
exit();