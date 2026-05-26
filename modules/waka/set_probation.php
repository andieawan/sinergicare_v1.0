<?php
// modules/waka/set_probation.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin', 'waka_kesiswaan']);

if (isset($_GET['id']) && isset($conn) && $conn !== null) {
    $student_id = (int)$_GET['id'];

    if ($student_id <= 0) {
        setFlash('error', '⚠️ Parameter ID siswa tidak valid!');
        header("Location: /pages/waka.php");
        exit();
    }

    try {
        // Cek eksistensi siswa sebelum update
        $stmt_check = $conn->prepare("SELECT id FROM students WHERE id = ? LIMIT 1");
        $stmt_check->execute([$student_id]);
        if (!$stmt_check->fetch()) {
            setFlash('error', '⚠️ Data siswa tidak ditemukan.');
            header("Location: /pages/waka.php");
            exit();
        }

        $stmt = $conn->prepare("
            UPDATE students 
            SET is_probation = 1, probation_end = DATE_ADD(NOW(), INTERVAL 30 DAY) 
            WHERE id = ?
        ");
        $stmt->execute([$student_id]);
        setFlash('success', '✅ Status Probation 30 hari siswa berhasil diaktifkan secara manual.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal mengatur status probation: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Parameter ID siswa tidak valid atau koneksi database bermasalah!');
}
header("Location: /pages/waka.php");
exit();