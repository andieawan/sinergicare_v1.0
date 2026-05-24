<?php
// database/reset_db.php
// PERBAIKAN C3 & H2: tambahkan proteksi sesi
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("403 Forbidden — Akses ditolak. Silakan login terlebih dahulu.");
}

$user_roles = (array)($_SESSION['user_roles'] ?? []);
if (!in_array('super_admin', $user_roles)) {
    http_response_code(403);
    die("403 Forbidden — Hanya Super Admin yang dapat mengakses halaman ini.");
}

define('DB_CONFIG_FILE', __DIR__ . '/../config/db_credentials.json');

if (file_exists(DB_CONFIG_FILE)) {
    unlink(DB_CONFIG_FILE);
    echo "✅ File db_credentials.json berhasil dihapus!<br>\n";
    echo "🔄 Silakan akses setup.php untuk setup database baru dengan schema yang benar.<br>\n";
} else {
    echo "ℹ️ File db_credentials.json tidak ditemukan.<br>\n";
}

echo '<meta http-equiv="refresh" content="2;url=/database/setup.php"/>';
