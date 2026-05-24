<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'bk']);

$user_id_login = currentUserId();

$students_attention  = [];
$active_consequences = [];
$letter_logs         = [];
$staf_list           = []; // BUG FIX: untuk dropdown penanggung_jawab di modal

if (isset($conn) && $conn !== null) {
    try {
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

        // BUG FIX: JOIN staf_sekolah untuk tampilkan nama penanggung_jawab
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

        $stmt_log = $conn->query("
            SELECT ls.*, s.nama AS nama_siswa, c.nama_kelas
            FROM log_surat ls
            JOIN students s ON ls.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE ls.tipe_surat = 'panggilan_ortu'
            ORDER BY ls.id DESC LIMIT 10
        ");
        if ($stmt_log) {
            $letter_logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);
        }

        // BUG FIX: ambil daftar staf untuk dropdown penanggung_jawab
        $stmt_staf = $conn->query("SELECT id, nama FROM staf_sekolah ORDER BY nama ASC");
        if ($stmt_staf) {
            $staf_list = $stmt_staf->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        $students_attention  = [];
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
// BUG FIX: include modal BK agar tombol + Konsekuensi berfungsi
require_once __DIR__ . '/../views/modals/bk.php';
require_once __DIR__ . '/../views/layouts/footer.php';
