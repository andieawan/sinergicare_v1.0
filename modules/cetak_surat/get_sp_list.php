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

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'sp_list' => []]);
    exit;
}

try {
    // Get approved SP for specific student
    $stmt = $conn->prepare("
        SELECT sp.id, sp.tingkat_sp, sp.alasan_sp, sp.created_at, sp.is_approved
        FROM sp_records sp
        WHERE sp.student_id = ? AND sp.is_approved = 1
        ORDER BY sp.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$student_id]);
    $sp_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'sp_list' => $sp_list
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
