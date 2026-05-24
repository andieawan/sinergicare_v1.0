<?php
// modules/waka/set_probation.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin', 'waka_kesiswaan']);

if (isset($_GET['id']) && isset($conn)) {
    $student_id = (int)$_GET['id'];
    try {
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
}
header("Location: /pages/waka.php");
exit();