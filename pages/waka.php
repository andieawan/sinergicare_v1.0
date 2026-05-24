<?php
// pages/waka.php
// PERBAIKAN H4: tambahkan JOIN staf_sekolah di query sp_records
//               agar nama pejabat bisa ditampilkan (bukan ID integer)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'waka_kesiswaan']);

$students_critical = [];
$sp_records        = [];

if (isset($conn) && $conn !== null) {
    try {
        // Query 1: siswa zona merah / ada SP / probation
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

        // PERBAIKAN H4: tambahkan JOIN ke staf_sekolah untuk mengambil nama pejabat penerbit SP
        // Sebelumnya kolom diterbitkan_oleh (INT) ditampilkan langsung sebagai nama
        $stmt_sp = $conn->query("
            SELECT sp.*,
                   s.nama AS nama_siswa,
                   c.nama_kelas,
                   st.nama AS nama_pejabat
            FROM sp_records sp
            JOIN students s ON sp.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN staf_sekolah st ON sp.diterbitkan_oleh = st.id
            ORDER BY sp.id DESC
        ");
        if ($stmt_sp) {
            $sp_records = $stmt_sp->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        $students_critical = [];
        $sp_records        = [];
    }
}

$pageTitle = 'Panel Kesiswaan (Waka)';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/waka/index.php';
require_once __DIR__ . '/../views/layouts/footer.php';
