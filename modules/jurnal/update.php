<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $id               = (int)($_POST['id'] ?? 0);
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $tanggal_kejadian = $_POST['tanggal_kejadian'] ?? date('Y-m-d');
    $lokasi_kejadian  = trim($_POST['lokasi_kejadian'] ?? '');
    $catatan          = trim($_POST['catatan'] ?? '');
    
    $user_id_login    = currentUserId();
    $user_roles    = currentUserRoles();
    $is_bk_admin   = count(array_intersect(['bk', 'admin', 'super_admin'], $user_roles)) > 0;

    try {
        // Ambil data insiden lama untuk pengecekan hak milik (ownership)
        $stmt_get = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
        $stmt_get->execute([$id]);
        $incident = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            setFlash('error', '⚠️ Catatan insiden tidak ditemukan.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        // Validasi Aturan Bisnis: Cek pembatasan hak akses edit
        $is_owner    = ($incident['user_id'] == $user_id_login);
        $within_time = (time() - strtotime($incident['created_at']) <= 1800); // 30 Menit

        if (!$is_bk_admin && !($is_owner && $within_time)) {
            setFlash('error', '🔒 Akses ditolak! Batas waktu edit mandiri (30 menit) telah kedaluwarsa.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        // Proses update data insiden
        $stmt_update = $conn->prepare("
            UPDATE incidents 
            SET category_id = ?, tanggal_kejadian = ?, lokasi_kejadian = ?, catatan = ? 
            WHERE id = ?
        ");
        $stmt_update->execute([$category_id, $tanggal_kejadian, $lokasi_kejadian, $catatan, $id]);

        // Hitung ulang kondisi radar siswa lama jika ada kemungkinan perubahan bobot risiko
        hitungUlangRadarSiswa($conn, (int)$incident['student_id']);

        setFlash('success', '✨ Catatan jurnal insiden berhasil diperbarui.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal memperbarui data: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode request tidak sah!');
}

header("Location: /pages/jurnal.php");
exit();