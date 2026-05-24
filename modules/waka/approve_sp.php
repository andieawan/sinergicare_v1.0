<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

// Proteksi Akses Sesi - Hanya Otoritas Manajemen Kesiswaan & Admin
requireLogin();
requireRole(['super_admin', 'admin', 'waka_kesiswaan']);

if (isset($_GET['id']) && isset($conn) && $conn !== null) {
    $sp_id = (int)$_GET['id'];

    try {
        // Ambil data draf SP untuk mengetahui ID siswa dan tingkatan SP yang diajukan
        $stmt_sp = $conn->prepare("SELECT student_id, tingkat_sp, is_approved FROM sp_records WHERE id = ? LIMIT 1");
        $stmt_sp->execute([$sp_id]);
        $sp_record = $stmt_sp->fetch(PDO::FETCH_ASSOC);

        if (!$sp_record) {
            setFlash('error', '⚠️ Berkas draf rekam data SP tidak dijumpai.');
            header("Location: /pages/waka.php");
            exit();
        }

        if ($sp_record['is_approved'] == 1) {
            setFlash('warning', '🔔 Berkas dokumen Surat Peringatan ini sudah berstatus disetujui sebelumnya.');
            header("Location: /pages/waka.php");
            exit();
        }

        $student_id = (int)$sp_record['student_id'];
        $tingkat_sp = $sp_record['tingkat_sp'];

        // --- MULAI DATABASE TRANSACTION (ATOMIC OPERATION) ---
        $conn->beginTransaction();

        // Kueri 1: Perbarui status berkas SP menjadi Approved (Disetujui)
        $stmt_app_sp = $conn->prepare("UPDATE sp_records SET is_approved = 1 WHERE id = ?");
        $stmt_app_sp->execute([$sp_id]);

        // Kueri 2: Naikkan status tingkat SP siswa & aktifkan masa probation 30 hari ke depan
        $stmt_app_stu = $conn->prepare("
            UPDATE students 
            SET status_sp = ?, is_probation = 1, probation_end = DATE_ADD(NOW(), INTERVAL 30 DAY) 
            WHERE id = ?
        ");
        $stmt_app_stu->execute([$tingkat_sp, $student_id]);

        // Komit seluruh rangkaian kueri ke dalam database jika aman tanpa interupsi
        $conn->commit();

        setFlash('success', '✅ Berkas Surat Peringatan resmi disetujui & Masa Probation 30 hari siswa telah aktif.');
    } catch (PDOException $e) {
        // Batalkan seluruh rangkaian perubahan data di atas jika salah satu kueri crash
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        setFlash('error', '⚠️ Gagal menyetujui pengajuan berkas: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Parameter ID berkas tidak valid!');
}

header("Location: /pages/waka.php");
exit();