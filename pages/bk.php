<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

// Proteksi Sesi dan Hak Akses Peran (Hanya BK & Admin)
requireLogin();
requireRole(['super_admin', 'admin', 'bk']);

$user_id_login = currentUserId();

$students_attention = [];
$active_consequences = [];
$letter_logs = [];

if (isset($conn) && $conn !== null) {
    try {
        // 1. Ambil daftar siswa di Zona Kuning atau Merah yang memerlukan intervensi BK
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

        // 2. Ambil daftar konsekuensi/tugas kedisiplinan yang sedang berjalan (status: proses)
        // PERINGATAN: Sesuai REFERENCE.md, tidak boleh menyeleksi atau menggunakan kolom bk_id!
        $stmt_con = $conn->query("
            SELECT co.*, s.nama AS nama_siswa, c.nama_kelas
            FROM consequences co
            JOIN students s ON co.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE co.status_tugas = 'proses'
            ORDER BY co.id DESC
        ");
        if ($stmt_con) {
            $active_consequences = $stmt_con->fetchAll(PDO::FETCH_ASSOC);
        }

        // 3. Ambil riwayat cetak log surat panggilan orang tua teratas
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

    } catch (Exception $e) {
        // Safe Fallback jika terjadi kendala pada kueri relasional database
        $students_attention = [];
        $active_consequences = [];
        $letter_logs = [];
    }
}

// Komposisi Struktur Kerangka Layout Utama SinergiCare
$pageTitle = 'Panel Bimbingan Konseling (BK)';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/bk/index.php';
require_once __DIR__ . '/../views/layouts/footer.php';