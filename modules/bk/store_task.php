<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

// Proteksi Sesi Keamanan Tingkat BK/Admin
requireLogin();
requireRole(['super_admin', 'admin', 'bk']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $student_id       = (int)($_POST['student_id'] ?? 0);
    $deskripsi_tugas  = trim($_POST['deskripsi_tugas'] ?? '');
    $penanggung_jawab = trim($_POST['penanggung_jawab'] ?? '');

    if ($student_id <= 0 || empty($deskripsi_tugas) || empty($penanggung_jawab)) {
        setFlash('error', '⚠️ Gagal! Seluruh parameter mandat tugas konsekuensi harus dilengkapi.');
        header("Location: /pages/bk.php");
        exit();
    }

    try {
        // PERBAIKAN BUG LAMA: Sesuai REFERENCE.md, jangan menyertakan kolom bk_id (kolom tidak ada)
        $stmt = $conn->prepare("
            INSERT INTO consequences (student_id, deskripsi_tugas, penanggung_jawab, status_tugas, created_at)
            VALUES (?, ?, ?, 'proses', NOW())
        ");
        $stmt->execute([$student_id, $deskripsi_tugas, $penanggung_jawab]);

        setFlash('success', '📋 Tugas konsekuensi disiplin pemulihan karakter siswa berhasil diterbitkan.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal menyimpan data konsekuensi: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request data tidak sah!');
}

header("Location: /pages/bk.php");
exit();