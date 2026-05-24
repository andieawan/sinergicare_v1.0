<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'bk']);

$user_id_login = currentUserId();

$students_attention  = [];
$all_students        = [];
$active_consequences = [];
$letter_logs         = [];
$staf_list           = [];

if (isset($conn) && $conn !== null) {
    try {
        // Query 1: Siswa dalam pantauan (kuning/merah)
        $stmt_stu = $conn->query("
            SELECT s.*, c.nama_kelas 
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.status_warna IN ('kuning', 'merah')
            ORDER BY FIELD(s.status_warna, 'merah', 'kuning'), s.nama ASC
        ");
        if ($stmt_stu) {
            $students_attention = $stmt_stu->fetchAll(PDO::FETCH_ASSOC);
        }

        // Query 2: Semua siswa untuk cetak surat
        $stmt_all = $conn->query("
            SELECT s.*, c.nama_kelas 
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            ORDER BY c.nama_kelas ASC, s.nama ASC
        ");
        if ($stmt_all) {
            $all_students = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
        }

        // Query 3: Tugas konsekuensi aktif + nama penanggung jawab
        $stmt_con = $conn->query("
            SELECT co.*, s.nama AS nama_siswa, c.nama_kelas,
                   ss.nama AS nama_penanggung_jawab
            FROM consequences co
            JOIN students s ON co.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN staf_sekolah ss ON co.penanggung_jawab = ss.id
            WHERE co.status_tugas = 'proses'
            ORDER BY co.id DESC
        ");
        if ($stmt_con) {
            $active_consequences = $stmt_con->fetchAll(PDO::FETCH_ASSOC);
        }

        // BUG FIX #1 & #4 & #20:
        // Sebelumnya query FROM log_surat dengan kolom 'dibuat_oleh',
        // tapi log_cetak.php INSERT ke 'log_cetak_surat' dengan kolom 'user_id'.
        // Diperbaiki: query ke log_cetak_surat dengan kolom yang benar.
        // Jika tabel log_cetak_surat belum ada (baru pertama jalan), tangani gracefully.
        try {
            $stmt_log = $conn->query("
                SELECT lcs.*, s.nama AS nama_siswa, c.nama_kelas,
                       lcs.tanggal_surat, lcs.jam_surat
                FROM log_cetak_surat lcs
                JOIN students s ON lcs.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE lcs.tipe_surat = 'panggilan_ortu'
                ORDER BY lcs.id DESC LIMIT 10
            ");
            if ($stmt_log) {
                $letter_logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            // Tabel log_cetak_surat belum ada — abaikan, tampilkan kosong
            $letter_logs = [];
        }

        // Query 5: Daftar staf untuk dropdown penanggung_jawab
        $stmt_staf = $conn->query("SELECT id, nama FROM staf_sekolah ORDER BY nama ASC");
        if ($stmt_staf) {
            $staf_list = $stmt_staf->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        $students_attention  = [];
        $all_students        = [];
        $active_consequences = [];
        $letter_logs         = [];
        $staf_list           = [];
    }
}

$pageTitle = 'Panel Bimbingan Konseling (BK)';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/bk/index.php';
require_once __DIR__ . '/../views/modals/bk.php';
require_once __DIR__ . '/../views/layouts/footer.php';