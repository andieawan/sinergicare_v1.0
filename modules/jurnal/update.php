<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $csrf             = $_POST['_csrf'] ?? '';
    $id               = (int)($_POST['id'] ?? 0);
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $tanggal_kejadian = trim((string)($_POST['tanggal_kejadian'] ?? date('Y-m-d')));
    $lokasi_kejadian  = trim($_POST['lokasi_kejadian'] ?? '');
    $catatan          = trim($_POST['catatan'] ?? '');
    
    $user_id_login = currentUserId();
    $user_roles    = currentUserRoles();
    $is_bk_admin   = count(array_intersect(['bk', 'admin', 'super_admin'], $user_roles)) > 0;

    if (!csrf_validate($csrf)) {
        setFlash('error', '🔒 Permintaan ditolak (CSRF token tidak valid).');
        header("Location: /pages/jurnal.php");
        exit();
    }

    if ($id <= 0 || $category_id <= 0 || $lokasi_kejadian === '' || $catatan === '') {
        setFlash('error', '⚠️ Data pembaruan jurnal tidak lengkap.');
        header("Location: /pages/jurnal.php");
        exit();
    }

    if (strlen($lokasi_kejadian) > 255 || strlen($catatan) > 2000) {
        setFlash('error', '⚠️ Data terlalu panjang. Lokasi maksimal 255 karakter dan catatan maksimal 2000 karakter.');
        header("Location: /pages/jurnal.php");
        exit();
    }

    $dt = DateTime::createFromFormat('Y-m-d', $tanggal_kejadian);
    if (!$dt || $dt->format('Y-m-d') !== $tanggal_kejadian) {
        setFlash('error', '⚠️ Format tanggal kejadian tidak valid.');
        header("Location: /pages/jurnal.php");
        exit();
    }

    try {
        // Validasi category_id harus ada
        $stmt_cat = $conn->prepare("SELECT id FROM violation_categories WHERE id = ? LIMIT 1");
        $stmt_cat->execute([$category_id]);
        if (!$stmt_cat->fetch(PDO::FETCH_ASSOC)) {
            setFlash('error', '⚠️ Jenis pelanggaran tidak valid.');
            header("Location: /pages/jurnal.php");
            exit();
        }

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
        $is_owner = ($incident['user_id'] == $user_id_login);
        
        // PERBAIKAN: Menggunakan DateTime dan DateTimeZone agar kalkulasi waktu tidak meleset akibat bias timezone server
        $tz             = new DateTimeZone(date_default_timezone_get());
        $waktu_sekarang = new DateTime('now', $tz);
        $waktu_dibuat   = new DateTime($incident['created_at'], $tz);
        
        // Hitung selisih waktu dalam satuan detik
        $selisih_detik  = $waktu_sekarang->getTimestamp() - $waktu_dibuat->getTimestamp();
        $within_time    = ($selisih_detik <= 1800 && $selisih_detik >= 0); // Maksimal 30 Menit (1800 detik)

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
        error_log('Jurnal update DB error: ' . $e->getMessage());
        setFlash('error', '⚠️ Terjadi gangguan sistem saat memperbarui data.');
    }
} else {
    setFlash('error', '⚠️ Metode request tidak sah!');
}

header("Location: /pages/jurnal.php");
exit();