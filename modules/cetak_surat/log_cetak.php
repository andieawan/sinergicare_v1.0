<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/functions.php';

requireLogin();

// Check permission
if (!hasRole(['super_admin', 'admin', 'bk'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

header('Content-Type: application/json');

try {
    // Get current user
    $user_id = currentUserId();
    if (!$user_id) {
        die(json_encode(['status' => 'error', 'message' => 'User not found']));
    }

    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $tipe_surat = isset($_POST['tipe_surat']) ? trim($_POST['tipe_surat']) : '';
    
    // Optional fields
    $tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : NULL;
    $jam = isset($_POST['jam']) ? trim($_POST['jam']) : NULL;

    if ($student_id <= 0 || empty($tipe_surat)) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid parameters']));
    }

    // Create table if not exists
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS log_cetak_surat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            tipe_surat VARCHAR(50) NOT NULL,
            tanggal_surat DATE,
            jam_surat TIME,
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            KEY idx_student (student_id),
            KEY idx_created (created_at)
        )
    ";
    
    try {
        $conn->exec($create_table_sql);
    } catch (PDOException $e) {
        // Table may already exist, ignore error
    }

    // Insert log
    $stmt = $conn->prepare("
        INSERT INTO log_cetak_surat (student_id, tipe_surat, tanggal_surat, jam_surat, user_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $student_id,
        $tipe_surat,
        $tanggal,
        $jam,
        $user_id
    ]);

    $log_id = $conn->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Log cetak surat berhasil tercatat',
        'log_id' => $log_id
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
