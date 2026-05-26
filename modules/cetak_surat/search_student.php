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

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'students' => []]);
    exit;
}

try {
    // Search by name or NISN
    $search_term = '%' . $query . '%';
    $stmt = $conn->prepare("
        SELECT s.id, s.nama, s.nisn, c.nama_kelas
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        WHERE s.nama LIKE ? OR s.nisn LIKE ?
        LIMIT 15
    ");
    $stmt->execute([$search_term, $search_term]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'students' => $students
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
