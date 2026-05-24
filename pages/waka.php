<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

// Proteksi Sesi dan Pembatasan Hak Akses Peran (Waka & Admin)
requireLogin();
requireRole(['super_admin', 'admin', 'waka_kesiswaan']);

$students_critical = [];
$sp_records = [];

if (isset($conn) && $conn !== null) {
    try {
        // 1. Ambil data siswa yang berada di Zona Merah atau sudah memiliki status SP/Probation
        $stmt_crit = $conn->query("
            SELECT s.*, c.nama_kelas 
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.status_warna = 'merah' OR s.status_sp != 'tidak_ada' OR s.is_probation = 1
            ORDER BY FIELD(s.status_warna, 'merah', 'kuning', 'hijau'), s.nama ASC
        ");
        if ($stmt_crit) {
            $students_critical = $stmt_crit->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2. Ambil seluruh riwayat penerbitan Surat Peringatan (SP) beserta relasi data siswa
        $stmt_sp = $conn->query("
            SELECT sp.*, s.nama AS nama_siswa, c.nama_kelas
            FROM sp_records sp
            JOIN students s ON sp.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            ORDER BY sp.id DESC
        ");
        if ($stmt_sp) {
            $sp_records = $stmt_sp->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        $students_critical = [];
        $sp_records = [];
    }
}

// Konfigurasi Kerangka Layout Komponen SinergiCare
$pageTitle = 'Panel Kesiswaan (Waka)';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/waka/index.php';
require_once __DIR__ . '/../views/layouts/footer.php';