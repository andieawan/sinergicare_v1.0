<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';

header('Content-Type: application/json');

// Proteksi API: Tolak request jika tidak memiliki sesi login aktif
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized API request.']);
    exit();
}

$student_id    = (int)($_POST['student_id'] ?? 0);
$tipe_surat    = trim($_POST['tipe_surat'] ?? '');
$tanggal_surat = $_POST['tanggal'] ?? '';
$jam_surat     = $_POST['jam'] ?? '';
$dibuat_oleh   = currentUserId();

if ($student_id <= 0 || empty($tipe_surat) || !isset($conn) || $conn === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameter or database connection error.']);
    exit();
}

try {
    // Memastikan skema tabel log_surat tersedia secara aman (Struktur sesuai REFERENCE.md)
    $conn->exec("CREATE TABLE IF NOT EXISTS `log_surat` (
        `id`            INT AUTO_INCREMENT PRIMARY KEY,
        `student_id`    INT NOT NULL,
        `tipe_surat`    VARCHAR(50) NOT NULL,
        `tanggal_surat` DATE NULL,
        `jam_surat`     TIME NULL,
        `dibuat_oleh`   INT NOT NULL,
        `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`)     ON DELETE CASCADE,
        FOREIGN KEY (`dibuat_oleh`) REFERENCES `staf_sekolah`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Lakukan pencatatan riwayat arsip cetak surat panggilan orang tua
    $stmt = $conn->prepare("
        INSERT INTO log_surat (student_id, tipe_surat, tanggal_surat, jam_surat, dibuat_oleh, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $student_id, 
        $tipe_surat, 
        !empty($tanggal_surat) ? $tanggal_surat : null, 
        !empty($jam_surat) ? $jam_surat : null, 
        $dibuat_oleh
    ]);

    echo json_encode(['status' => 'success']);
    exit();
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'PDO Exception: ' . $e->getMessage()]);
    exit();
}