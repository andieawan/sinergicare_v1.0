<?php
// modules/cetak_surat/log_cetak.php
// BUG FIX #5 & #8: Unifikasi ke tabel log_surat (dari setup.php).
// Identik dengan modules/bk/log_cetak.php — keduanya sekarang INSERT ke tabel yang sama.

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();

if (!hasRole(['super_admin', 'admin', 'bk'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

header('Content-Type: application/json');

try {
    $user_id    = currentUserId();
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $tipe_surat = isset($_POST['tipe_surat'])  ? trim($_POST['tipe_surat'])  : '';
    $tanggal    = !empty($_POST['tanggal'])    ? trim($_POST['tanggal'])    : null;
    $jam        = !empty($_POST['jam'])        ? trim($_POST['jam'])        : null;

    if (!$user_id) {
        die(json_encode(['status' => 'error', 'message' => 'User session tidak valid']));
    }

    if ($student_id <= 0 || empty($tipe_surat)) {
        die(json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']));
    }

    // Pastikan siswa ada
    $stmt_check = $conn->prepare("SELECT id FROM students WHERE id = ? LIMIT 1");
    $stmt_check->execute([$student_id]);
    if (!$stmt_check->fetch()) {
        die(json_encode(['status' => 'error', 'message' => 'Siswa tidak ditemukan']));
    }

    // INSERT ke tabel log_surat (sudah dibuat di setup.php)
    $stmt = $conn->prepare("
        INSERT INTO log_surat (student_id, tipe_surat, tanggal_surat, jam_surat, dibuat_oleh, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$student_id, $tipe_surat, $tanggal, $jam, $user_id]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Log cetak surat berhasil tercatat',
        'log_id'  => (int)$conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}