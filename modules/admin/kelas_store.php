<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $nama_kelas = trim($_POST['nama_kelas'] ?? '');

    if (empty($nama_kelas)) {
        setFlash('error', '⚠️ Gagal! Nama kelas tidak boleh kosong.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek duplikasi nama kelas
        $stmt_check = $conn->prepare("SELECT id FROM classes WHERE nama_kelas = ? LIMIT 1");
        $stmt_check->execute([$nama_kelas]);
        if ($stmt_check->fetch()) {
            setFlash('error', "⚠️ Gagal! Kelas '{$nama_kelas}' sudah terdaftar.");
            header("Location: /pages/admin.php");
            exit();
        }

        // Simpan data kelas baru
        $stmt_insert = $conn->prepare("INSERT INTO classes (nama_kelas) VALUES (?)");
        $stmt_insert->execute([$nama_kelas]);

        setFlash('success', "✨ Sukses! Kelas '{$nama_kelas}' berhasil ditambahkan.");
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal menyimpan data kelas: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request data tidak sah!');
}

header("Location: /pages/admin.php");
exit();