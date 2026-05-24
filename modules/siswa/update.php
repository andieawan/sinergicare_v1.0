<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $id       = (int)($_POST['siswa_id'] ?? 0);
    $nisn     = trim($_POST['nisn'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $class_id = (int)($_POST['class_id'] ?? 0);

    if ($id <= 0 || empty($nisn) || empty($nama) || $class_id <= 0) {
        setFlash('error', '⚠️ Gagal memperbarui! Input parameter tidak lengkap.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek keberadaan rekam data siswa
        $stmt_exist = $conn->prepare("SELECT id FROM students WHERE id = ?");
        $stmt_exist->execute([$id]);
        if (!$stmt_exist->fetch()) {
            setFlash('error', '⚠️ Catatan profil siswa tidak ditemukan.');
            header("Location: /pages/admin.php");
            exit();
        }

        // Validasi Duplikasi NISN pada entitas siswa yang berbeda
        $stmt_check = $conn->prepare("SELECT id FROM students WHERE nisn = ? AND id != ? LIMIT 1");
        $stmt_check->execute([$nisn, $id]);
        if ($stmt_check->fetch()) {
            setFlash('error', "⚠️ Gagal! NISN {$nisn} sudah dialokasikan untuk siswa lain.");
            header("Location: /pages/admin.php");
            exit();
        }

        // Eksekusi pembaruan data struktural
        $stmt_update = $conn->prepare("UPDATE students SET nisn = ?, nama = ?, class_id = ? WHERE id = ?");
        $stmt_update->execute([$nisn, $nama, $class_id, $id]);

        setFlash('success', '✨ Profil informasi siswa berhasil diperbarui.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memperbarui data: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request tidak diizinkan!');
}

header("Location: /pages/admin.php");
exit();