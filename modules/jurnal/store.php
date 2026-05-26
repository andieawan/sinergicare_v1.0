<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'bk', 'guru', 'waka_kesiswaan', 'kepala_jurusan']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $csrf            = $_POST['_csrf'] ?? '';
    $student_info     = trim($_POST['student_info'] ?? '');
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $tanggal_kejadian = trim((string)($_POST['tanggal_kejadian'] ?? date('Y-m-d')));
    $lokasi_kejadian  = trim($_POST['lokasi_kejadian'] ?? '');
    $catatan          = trim($_POST['catatan'] ?? '');
    $user_id_login    = currentUserId();

    if (!csrf_validate($csrf)) {
        setFlash('error', '🔒 Permintaan ditolak (CSRF token tidak valid).');
        header("Location: /pages/jurnal.php");
        exit();
    }

    if (empty($student_info) || empty($category_id) || empty($lokasi_kejadian) || empty($catatan)) {
        setFlash('error', '⚠️ Seluruh kolom formulir wajib diisi dengan lengkap!');
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
        // Validasi format penulisan dari datalist autocomplete
        if (strpos($student_info, ' - ') === false) {
            setFlash('error', '⚠️ Identitas siswa tidak valid! Pilih dari daftar rekomendasi, jangan ketik manual.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        $parts = explode(' - ', $student_info);
        $nisn  = trim($parts[0] ?? '');

        // PERBAIKAN: Menambahkan validasi panjang karakter (wajib 10 digit) serta memperjelas pesan error
        if (empty($nisn) || !ctype_digit($nisn) || strlen($nisn) !== 10) {
            setFlash('error', '⚠️ Format NISN tidak valid! NISN harus berupa 10 digit angka murni (contoh: 0012345678). Silakan pilih ulang dari daftar rekomendasi.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        // Validasi category_id harus ada
        $stmt_cat = $conn->prepare("SELECT id FROM violation_categories WHERE id = ? LIMIT 1");
        $stmt_cat->execute([$category_id]);
        if (!$stmt_cat->fetch(PDO::FETCH_ASSOC)) {
            setFlash('error', '⚠️ Jenis pelanggaran tidak valid.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        // Cari ID Siswa berdasarkan NISN hasil parsing
        $stmt_check = $conn->prepare("SELECT id FROM students WHERE nisn = ? LIMIT 1");
        $stmt_check->execute([$nisn]);
        $student = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            setFlash('error', '⚠️ Siswa dengan NISN ' . htmlspecialchars($nisn, ENT_QUOTES, 'UTF-8') . ' tidak ditemukan di sistem.');
            header("Location: /pages/jurnal.php");
            exit();
        }

        $student_id = (int)$student['id'];

        // Simpan data insiden baru ke database
        $stmt_insert = $conn->prepare("
            INSERT INTO incidents (student_id, category_id, user_id, catatan, lokasi_kejadian, tanggal_kejadian, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt_insert->execute([$student_id, $category_id, $user_id_login, $catatan, $lokasi_kejadian, $tanggal_kejadian]);

        // Pemicu otomatis hitung ulang kondisi warna radar siswa
        hitungUlangRadarSiswa($conn, $student_id);

        setFlash('success', '✨ Laporan insiden berhasil dicatat ke dalam jurnal operasional.');
    } catch (PDOException $e) {
        error_log('Jurnal store DB error: ' . $e->getMessage());
        setFlash('error', '⚠️ Terjadi gangguan sistem saat menyimpan laporan.');
    }
} else {
    setFlash('error', '⚠️ Metode pengiriman data tidak valid!');
}

header("Location: /pages/jurnal.php");
exit();