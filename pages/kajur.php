<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

// Proteksi Sesi dan Pembatasan Hak Akses Peran Kajur
requireLogin();
requireRole(['super_admin', 'admin', 'kepala_jurusan']);

$rekap_kelas_jurusan = [];
$siswa_berisiko_jurusan = [];

if (isset($conn) && $conn !== null) {
    try {
        // 1. Mengambil data akumulasi status warna radar per kelas untuk analisis makro Kajur
        $stmt_rekap = $conn->query("
            SELECT 
                c.id AS class_id,
                c.nama_kelas,
                COUNT(s.id) AS total_siswa,
                SUM(CASE WHEN s.status_warna = 'hijau' THEN 1 ELSE 0 END) AS jumlah_hijau,
                SUM(CASE WHEN s.status_warna = 'kuning' THEN 1 ELSE 0 END) AS jumlah_kuning,
                SUM(CASE WHEN s.status_warna = 'merah' THEN 1 ELSE 0 END) AS jumlah_merah
            FROM classes c
            LEFT JOIN students s ON c.id = s.class_id
            GROUP BY c.id
            ORDER BY c.nama_kelas ASC
        ");
        if ($stmt_rekap) {
            $rekap_kelas_jurusan = $stmt_rekap->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2. Mengambil daftar siswa yang berada di Zona Kuning & Merah untuk penanganan tingkat jurusan
        $stmt_siswa = $conn->query("
            SELECT s.*, c.nama_kelas
            FROM students s
            JOIN classes c ON s.class_id = c.id
            WHERE s.status_warna IN ('kuning', 'merah')
            ORDER BY FIELD(s.status_warna, 'merah', 'kuning'), s.nama ASC
        ");
        if ($stmt_siswa) {
            $siswa_berisiko_jurusan = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        $rekap_kelas_jurusan = [];
        $siswa_berisiko_jurusan = [];
    }
}

// Konfigurasi Header Layout Dinamis SinergiCare
$pageTitle = 'Panel Ketua Jurusan';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/kajur/index.php';
require_once __DIR__ . '/../views/layouts/footer.php';