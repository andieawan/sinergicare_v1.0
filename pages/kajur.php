<?php
// pages/kajur.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'kepala_jurusan']);

$rekap_kelas_jurusan = [];
$siswa_berisiko_jurusan = [];

// Asumsi: ID Jurusan dari guru yang login disimpan di dalam session atau data staf
$jurusan_id_staf = $_SESSION['jurusan_id'] ?? 0; 

if (isset($conn) && $conn !== null) {
    try {
        // QUERY 1: Memenuhi matriks rekapitulasi per kelas menggunakan conditional aggregation
        $stmt_rekap = $conn->prepare("
            SELECT 
                c.nama_kelas,
                COUNT(s.id) AS total_siswa,
                SUM(CASE WHEN s.status_warna = 'hijau' THEN 1 ELSE 0 END) AS jumlah_hijau,
                SUM(CASE WHEN s.status_warna = 'kuning' THEN 1 ELSE 0 END) AS jumlah_kuning,
                SUM(CASE WHEN s.status_warna = 'merah' THEN 1 ELSE 0 END) AS jumlah_merah
            FROM classes c
            LEFT JOIN students s ON c.id = s.class_id
            WHERE c.jurusan_id = ?
            GROUP BY c.id, c.nama_kelas
            ORDER BY c.nama_kelas ASC
        ");
        $stmt_rekap->execute([$jurusan_id_staf]);
        $rekap_kelas_jurusan = $stmt_rekap->fetchAll(PDO::FETCH_ASSOC);

        // QUERY 2: Memenuhi direktori pengawasan siswa berisiko (Kuning & Merah)
        $stmt_risiko = $conn->prepare("
            SELECT 
                s.nama, 
                s.nisn, 
                s.status_warna, 
                s.level_eskalasi, 
                s.status_sp,
                c.nama_kelas
            FROM students s
            JOIN classes c ON s.class_id = c.id
            WHERE c.jurusan_id = ? 
              AND s.status_warna IN ('kuning', 'merah')
            ORDER BY FIELD(s.status_warna, 'merah', 'kuning'), s.nama ASC
        ");
        $stmt_risiko->execute([$jurusan_id_staf]);
        $siswa_berisiko_jurusan = $stmt_risiko->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Fallback aman agar halaman tidak crash total jika terjadi gangguan database
        error_log("Kajur Panel SQL Error: " . $e->getMessage());
        $rekap_kelas_jurusan = [];
        $siswa_berisiko_jurusan = [];
    }
}

// Render layout views
$pageTitle = 'Panel Kepala Jurusan';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/kajur/index.php'; // Berkas view yang Anda upload
require_once __DIR__ . '/../views/layouts/footer.php';