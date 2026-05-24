<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

// Memastikan pengguna sudah terautentikasi sebelum mengakses dashboard
requireLogin();

$user_id_login = currentUserId();
$user_roles    = currentUserRoles();

// --- [QUERY A: STATISTIK WARNA RADAR] ---
$count_hijau = 0; $count_kuning = 0; $count_merah = 0;
if (isset($conn) && $conn !== null) {
    $q_stats = $conn->query("SELECT status_warna, COUNT(*) as jumlah FROM students GROUP BY status_warna");
    if ($q_stats) {
        while($row = $q_stats->fetch(PDO::FETCH_ASSOC)) {
            if($row['status_warna'] == 'hijau')  $count_hijau  = $row['jumlah'];
            if($row['status_warna'] == 'kuning') $count_kuning = $row['jumlah'];
            if($row['status_warna'] == 'merah')  $count_merah  = $row['jumlah'];
        }
    }
}

// --- [QUERY F: LOG KASUS JURNAL TERKINI] ---
$log_jurnal_terkini = [];
if (isset($conn) && $conn !== null) {
    try {
        $is_bk_admin = count(array_intersect(['bk', 'admin', 'super_admin'], $user_roles)) > 0;
        
        if ($is_bk_admin) {
            // BK dan Admin melihat semua insiden yang dilaporkan hari ini
            $q_log = $conn->prepare("SELECT i.*, s.nama AS nama_siswa, c.nama_kelas, vc.nama_kejadian, vc.bobot_risiko 
                                     FROM incidents i 
                                     JOIN students s ON i.student_id = s.id 
                                     LEFT JOIN classes c ON s.class_id = c.id 
                                     JOIN violation_categories vc ON i.category_id = vc.id 
                                     WHERE DATE(i.created_at) = CURDATE()
                                     ORDER BY i.id DESC");
            $q_log->execute();
        } else {
            // Guru umum hanya melihat 15 insiden mandiri yang mereka laporkan
            $q_log = $conn->prepare("SELECT i.*, s.nama AS nama_siswa, c.nama_kelas, vc.nama_kejadian, vc.bobot_risiko 
                                     FROM incidents i 
                                     JOIN students s ON i.student_id = s.id 
                                     LEFT JOIN classes c ON s.class_id = c.id 
                                     JOIN violation_categories vc ON i.category_id = vc.id 
                                     WHERE i.user_id = :user_id
                                     ORDER BY i.id DESC LIMIT 15");
            $q_log->execute(['user_id' => $user_id_login]);
        }

        while($r_log = $q_log->fetch(PDO::FETCH_ASSOC)) {
            $log_jurnal_terkini[] = $r_log;
        }
    } catch (Exception $e) {
        $log_jurnal_terkini = [];
    }
}

// --- [QUERY G: PETA KERAWANAN KELAS] ---
$peta_kerawanan_kelas = [];
if (isset($conn) && $conn !== null) {
    $q_rawan = $conn->query("SELECT c.nama_kelas, COUNT(i.id) as total_cases 
                             FROM incidents i
                             JOIN students s ON i.student_id = s.id
                             JOIN classes c ON s.class_id = c.id
                             GROUP BY c.id ORDER BY total_cases DESC LIMIT 3");
    if ($q_rawan) {
        while($r_rawan = $q_rawan->fetch(PDO::FETCH_ASSOC)) {
            $peta_kerawanan_kelas[] = $r_rawan;
        }
    }
}

// --- [QUERY H: TREN PELANGGARAN BULAN INI] ---
$tren_pelanggaran = [];
if (isset($conn) && $conn !== null) {
    $q_tren = $conn->query("SELECT vc.nama_kejadian, COUNT(i.id) as jumlah 
                            FROM incidents i
                            JOIN violation_categories vc ON i.category_id = vc.id
                            WHERE MONTH(i.created_at) = MONTH(CURRENT_DATE())
                            GROUP BY vc.id ORDER BY jumlah DESC LIMIT 3");
    if ($q_tren) {
        while($r_tren = $q_tren->fetch(PDO::FETCH_ASSOC)) {
            $tren_pelanggaran[] = $r_tren;
        }
    }
}

// Eksekusi Struktur Layout Global SinergiCare
$pageTitle = 'Dashboard Overview';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/dashboard/index.php';
require_once __DIR__ . '/../views/layouts/footer.php';