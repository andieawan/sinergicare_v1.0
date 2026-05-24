<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

// Proteksi Sesi Keamanan Tingkat BK/Admin
requireLogin();
requireRole(['super_admin', 'admin', 'bk']);

if (isset($_GET['id']) && isset($conn) && $conn !== null) {
    $id = (int)$_GET['id'];

    try {
        // Cek eksistensi keaktifan data konsekuensi tugas
        $stmt_check = $conn->prepare("SELECT id FROM consequences WHERE id = ? LIMIT 1");
        $stmt_check->execute([$id]);
        
        if (!$stmt_check->fetch()) {
            setFlash('error', '⚠️ Berkas rekaman tugas konsekuensi tidak ditemukan.');
            header("Location: /pages/bk.php");
            exit();
        }

        // Jalankan pembaruan status kerja tugas beserta timestamp penyelesaian
        $stmt_update = $conn->prepare("
            UPDATE consequences 
            SET status_tugas = 'selesai', completed_at = NOW() 
            WHERE id = ?
        ");
        $stmt_update->execute([$id]);

        setFlash('success', '✨ Sukses! Tugas konsekuensi pemulihan karakter siswa berhasil divalidasi.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memperbarui status tugas: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Parameter ID Konsekuensi tidak valid!');
}

header("Location: /pages/bk.php");
exit();