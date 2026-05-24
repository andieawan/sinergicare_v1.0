<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin', 'bk', 'guru', 'waka_kesiswaan', 'kepala_jurusan']);

$user_id_login = currentUserId();
$user_roles    = currentUserRoles();
$is_bk_admin   = count(array_intersect(['bk', 'admin', 'super_admin'], $user_roles)) > 0;

$categories = [];
$students   = [];
$incidents  = [];

if (isset($conn) && $conn !== null) {
    try {
        $stmt_cat = $conn->query("SELECT id, nama_kejadian, bobot_risiko FROM violation_categories ORDER BY nama_kejadian ASC");
        if ($stmt_cat) {
            $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt_stu = $conn->query("SELECT s.id, s.nisn, s.nama, c.nama_kelas FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.nama ASC");
        if ($stmt_stu) {
            $students = $stmt_stu->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($is_bk_admin || in_array('waka_kesiswaan', $user_roles) || in_array('kepala_jurusan', $user_roles)) {
            $stmt_inc = $conn->prepare("
                SELECT i.*, s.nama AS nama_siswa, c.nama_kelas, vc.nama_kejadian, vc.bobot_risiko, st.nama AS nama_pelapor
                FROM incidents i
                JOIN students s ON i.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                JOIN violation_categories vc ON i.category_id = vc.id
                LEFT JOIN staf_sekolah st ON i.user_id = st.id
                ORDER BY i.id DESC
            ");
            $stmt_inc->execute();
        } else {
            $stmt_inc = $conn->prepare("
                SELECT i.*, s.nama AS nama_siswa, c.nama_kelas, vc.nama_kejadian, vc.bobot_risiko, st.nama AS nama_pelapor
                FROM incidents i
                JOIN students s ON i.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                JOIN violation_categories vc ON i.category_id = vc.id
                LEFT JOIN staf_sekolah st ON i.user_id = st.id
                WHERE i.user_id = :user_id
                ORDER BY i.id DESC
            ");
            $stmt_inc->execute(['user_id' => $user_id_login]);
        }
        $incidents = $stmt_inc->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $incidents = [];
    }
}

$pageTitle = 'Jurnal Insiden Kedisiplinan';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/jurnal/index.php';
// BUG FIX: include modal jurnal agar tombol Edit berfungsi
require_once __DIR__ . '/../views/modals/jurnal.php';
require_once __DIR__ . '/../views/layouts/footer.php';
