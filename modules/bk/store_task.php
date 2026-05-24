<?php
// modules/bk/store_task.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin', 'bk']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $student_id       = (int)($_POST['student_id'] ?? 0);
    $deskripsi_tugas  = trim($_POST['deskripsi_tugas'] ?? '');
    $penanggung_jawab = (int)trim($_POST['penanggung_jawab'] ?? 0);

    if ($student_id <= 0 || empty($deskripsi_tugas) || $penanggung_jawab <= 0) {
        setFlash('error', '⚠️ Gagal! Seluruh parameter mandat tugas konsekuensi harus dilengkapi.');
        header("Location: /pages/bk.php");
        exit();
    }

    try {
        // PERBAIKAN C4: nilai status_tugas diubah ke 'proses' dan dikunci sebagai konstanta
        // untuk menghindari inkonsistensi dengan query di pages/bk.php
        // Query filter di bk.php: WHERE co.status_tugas = 'proses' — HARUS sama
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
