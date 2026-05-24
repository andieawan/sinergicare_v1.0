<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();

if (isset($_GET['id']) && isset($conn) && $conn !== null) {
    $id            = (int)$_GET['id'];
    $user_id_login = currentUserId();
    $user_roles    = currentUserRoles();
    $is_bk_admin   = count(array_intersect(['bk', 'admin', 'super_admin'], $user_roles)) > 0;

    try {
        // Ambil info insiden sebelum dihapus guna mendapatkan student_id
        $stmt_get = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
        $stmt_get->execute([$id]);
        $incident = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            setFlash('error', '⚠️ Data insiden tidak ditemukan.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        // Validasi Aturan Bisnis: Cek pembatasan hak akses hapus
        $is_owner    = ($incident['user_id'] == $user_id_login);
        $within_time = (time() - strtotime($incident['created_at']) <= 1800);

        if (!$is_bk_admin && !($is_owner && $within_time)) {
            setFlash('error', '🔒 Akses ditolak! Anda tidak memiliki otoritas menghapus rekam kasus ini.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        // Eksekusi penghapusan rekam kasus
        $stmt_del = $conn->prepare("DELETE FROM incidents WHERE id = ?");
        $stmt_del->execute([$id]);

        // Hitung ulang kondisi radar karena satu faktor risiko telah dihapus
        hitungUlangRadarSiswa($conn, (int)$incident['student_id']);

        setFlash('success', '✨ Rekam kasus berhasil dihapus dari jurnal operasional sekolah.');
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal menghapus rekam kasus: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Parameter ID tidak valid!');
}

header("Location: /pages/jurnal.php");
exit();