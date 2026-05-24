<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

// Proteksi Akses Sesi - Hanya Otoritas Kesiswaan & Admin
requireLogin();
requireRole(['super_admin', 'admin', 'waka_kesiswaan']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $student_id  = (int)($_POST['student_id'] ?? 0);
    $tingkat_sp  = trim($_POST['tingkat_sp'] ?? '');
    $alasan_sp   = trim($_POST['alasan_sp'] ?? '');
    $user_id_log = currentUserId();

    // Validasi input: alasan_sp tidak boleh kosong (NOT NULL di database)
    if ($student_id <= 0 || empty($tingkat_sp) || empty($alasan_sp)) {
        setFlash('error', '⚠️ Gagal! Tingkatan SP dan Alasan dasar hukum penerbitan wajib diisi.');
        header("Location: /pages/waka.php");
        exit();
    }

    try {
        // Cek keberadaan profil siswa
        $stmt_check = $conn->prepare("SELECT id, nama FROM students WHERE id = ? LIMIT 1");
        $stmt_check->execute([$student_id]);
        $siswa = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$siswa) {
            setFlash('error', '⚠️ Gagal! Data identitas siswa tidak ditemukan.');
            header("Location: /pages/waka.php");
            exit();
        }

        // Masukkan rekam draf SP baru (default is_approved = 0, menunggu persetujuan/approval resmi)
        $stmt_insert = $conn->prepare("
            INSERT INTO sp_records (student_id, tingkat_sp, alasan_sp, is_approved, diterbitkan_oleh, created_at)
            VALUES (?, ?, ?, 0, ?, NOW())
        ");
        $stmt_insert->execute([$student_id, $tingkat_sp, $alasan_sp, $user_id_log]);

        setFlash('success', '✨ Berkas draf Surat Peringatan berhasil diajukan. Menunggu approval sistem.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memproses penerbitan SP: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request data tidak sah!');
}

header("Location: /pages/waka.php");
exit();