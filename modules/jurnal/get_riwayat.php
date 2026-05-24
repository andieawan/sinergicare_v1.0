<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';

header('Content-Type: application/json');

// Proteksi API: Hanya pengguna login yang dapat melakukan request
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id > 0 && isset($conn) && $conn !== null) {
    try {
        $stmt = $conn->prepare("
            SELECT i.*, vc.nama_kejadian, vc.bobot_risiko 
            FROM incidents i 
            JOIN violation_categories vc ON i.category_id = vc.id 
            WHERE i.student_id = ? 
            ORDER BY i.id DESC
        ");
        $stmt->execute([$student_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
exit();