<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'bk', 'guru', 'waka_kesiswaan', 'kepala_jurusan']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $student_info     = trim($_POST['student_info'] ?? '');
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $tanggal_kejadian = $_POST['tanggal_kejadian'] ?? date('Y-m-d');
    $lokasi_kejadian  = trim($_POST['lokasi_kejadian'] ?? '');
    $catatan          = trim($_POST['catatan'] ?? '');
    $user_id_login    = currentUserId();

    if (empty($student_info) || empty($category_id) || empty($lokasi_kejadian) || empty($catatan)) {
        setFlash('error', '⚠️ Seluruh kolom formulir wajib diisi dengan lengkap!');
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

        // Cari ID Siswa berdasarkan NISN hasil parsing
        $stmt_check = $conn->prepare("SELECT id FROM students WHERE nisn = ? LIMIT 1");
        $stmt_check->execute([$nisn]);
        $student = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            // Mengubah pesan error agar lebih deskriptif ketika database mengembalikan nilai kosong
            setFlash('error', '⚠️ Siswa dengan NISN ' . htmlspecialchars($nisn) . ' tidak ditemukan di sistem! Pastikan data rekomendasi belum kedaluwarsa.');
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
        setFlash('error', '⚠️ Gagal menyimpan laporan: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode pengiriman data tidak valid!');
}

header("Location: /pages/jurnal.php");
exit();