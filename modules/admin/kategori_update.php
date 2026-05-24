<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $id            = (int)($_POST['id'] ?? 0);
    $nama_kejadian = trim($_POST['nama_kejadian'] ?? '');
    $bobot_risiko  = trim($_POST['bobot_risiko'] ?? '');

    if ($id <= 0 || empty($nama_kejadian) || !in_array($bobot_risiko, ['ringan', 'sedang', 'berat'])) {
        setFlash('error', '⚠️ Gagal! Poin pembaharuan kategori aturan tidak lengkap.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Cek duplikasi teks kejadian pada entitas ID yang berbeda
        $stmt_check = $conn->prepare("SELECT id FROM violation_categories WHERE nama_kejadian = ? AND id != ? LIMIT 1");
        $stmt_check->execute([$nama_kejadian, $id]);
        if ($stmt_check->fetch()) {
            setFlash('error', '⚠️ Gagal! Penamaan kategori kejadian tersebut sudah terpakai.');
            header("Location: /pages/admin.php");
            exit();
        }

        // Jalankan update
        $stmt_update = $conn->prepare("UPDATE violation_categories SET nama_kejadian = ?, bobot_risiko = ? WHERE id = ?");
        $stmt_update->execute([$nama_kejadian, $bobot_risiko, $id]);

        setFlash('success', '✨ Berhasil memperbarui regulasi master kategori kejadian.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memperbarui aturan kategori: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request tidak sah!');
}

header("Location: /pages/admin.php");
exit();