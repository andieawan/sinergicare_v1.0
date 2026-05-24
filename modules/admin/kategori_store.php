<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $nama_kejadian = trim($_POST['nama_kejadian'] ?? '');
    $bobot_risiko  = trim($_POST['bobot_risiko'] ?? '');

    if (empty($nama_kejadian) || !in_array($bobot_risiko, ['ringan', 'sedang', 'berat'])) {
        setFlash('error', '⚠️ Gagal! Input bentuk kejadian atau klasifikasi risiko tidak valid.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek duplikasi kategori kasus
        $stmt_check = $conn->prepare("SELECT id FROM violation_categories WHERE nama_kejadian = ? LIMIT 1");
        $stmt_check->execute([$nama_kejadian]);
        if ($stmt_check->fetch()) {
            setFlash('error', '⚠️ Gagal! Bentuk kategori kejadian tersebut sudah ada.');
            header("Location: /pages/admin.php");
            exit();
        }

        // Masukkan aturan kategori baru
        $stmt_insert = $conn->prepare("INSERT INTO violation_categories (nama_kejadian, bobot_risiko) VALUES (?, ?)");
        $stmt_insert->execute([$nama_kejadian, $bobot_risiko]);

        setFlash('success', '✨ Kategori regulasi perilaku baru berhasil diterbitkan.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal menyimpan aturan kategori: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode pengiriman data tidak valid!');
}

header("Location: /pages/admin.php");
exit();