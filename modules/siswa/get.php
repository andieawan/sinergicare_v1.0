<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';

header('Content-Type: application/json');

// Proteksi API: Hanya pengguna tersertifikasi yang dapat menarik data siswa
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($conn) && $conn !== null) {
    try {
        // Kasus A: Ambil detail data satu siswa secara spesifik berdasarkan ID
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $stmt = $conn->prepare("
                SELECT s.*, c.nama_kelas 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.id = ? LIMIT 1
            ");
            $stmt->execute([(int)$_GET['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } 
        // Kasus B: Ambil daftar siswa berdasarkan filter Nama Kelas
        elseif (isset($_GET['kelas']) && !empty($_GET['kelas'])) {
            $stmt = $conn->prepare("
                SELECT s.id, s.nama, s.nisn, s.class_id, c.nama_kelas 
                FROM students s 
                JOIN classes c ON s.class_id = c.id 
                WHERE c.nama_kelas = :nama_kelas
                ORDER BY s.nama ASC
            ");
            $stmt->execute(['nama_kelas' => $_GET['kelas']]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        // Kasus C: Ambil seluruh daftar siswa aktif secara global
        else {
            $stmt = $conn->query("
                SELECT s.id, s.nama, s.nisn, s.class_id, c.nama_kelas, s.status_warna
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                ORDER BY s.nama ASC
            ");
            $result = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Database connection unavailable']);
}
exit();