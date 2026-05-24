<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();
requireRole(['super_admin', 'admin']);

$kelas_list = [];
$staf_list  = [];
$kat_list   = [];

if (isset($conn) && $conn !== null) {
    try {
        // 1. Data seluruh kelas aktif
        $q_kelas = $conn->query("SELECT * FROM classes ORDER BY nama_kelas ASC");
        $kelas_list = $q_kelas ? $q_kelas->fetchAll(PDO::FETCH_ASSOC) : [];

        // 2. Data staf sekolah
        // BUG FIX: $conn->conn->query() → $conn->query() langsung
        $q_staf = $conn->query("SELECT id, nama, email, username, roles FROM staf_sekolah ORDER BY roles ASC, nama ASC");
        $staf_list = $q_staf ? $q_staf->fetchAll(PDO::FETCH_ASSOC) : [];

        // 3. Master kategori pelanggaran
        $q_kat = $conn->query("SELECT * FROM violation_categories ORDER BY nama_kejadian ASC");
        $kat_list = $q_kat ? $q_kat->fetchAll(PDO::FETCH_ASSOC) : [];

    } catch (Exception $e) {
        $kelas_list = [];
        $staf_list  = [];
        $kat_list   = [];
    }
}

$pageTitle = 'Kontrol Administrasi';
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
require_once __DIR__ . '/../views/admin/index.php';
// BUG FIX: include modal admin agar tombol Tambah/Edit berfungsi
require_once __DIR__ . '/../views/modals/admin.php';
require_once __DIR__ . '/../views/layouts/footer.php';
