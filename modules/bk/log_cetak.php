<?php
// modules/bk/log_cetak.php

// Panggil konfigurasi database dan sistem otentikasi terpusat
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';

// Pastikan header keluarannya selalu berupa format data JSON
header('Content-Type: application/json; charset=utf-8');

// Proteksi Keamanan Akses Sesi - Wajib sudah login
requireLogin();

// Validasi metode pengiriman data harus POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error', 
        'message' => '⚠️ Metode pengiriman data tidak sah!'
    ]);
    exit();
}

// Tangkap payload parameter dari FormData / POST request
$student_id    = (int)($_POST['student_id'] ?? 0);
$tipe_surat    = trim($_POST['tipe_surat'] ?? '');
$tanggal_surat = $_POST['tanggal'] ?? null;
$jam_surat     = $_POST['jam'] ?? null;
$dibuat_oleh   = currentUserId(); // Mengambil ID staf/user yang sedang login saat ini

// Validasi Kelayakan Parameter Utama
if ($student_id <= 0 || empty($tipe_surat)) {
    echo json_encode([
        'status' => 'error', 
        'message' => '⚠️ Gagal memproses! Parameter data tidak lengkap.'
    ]);
    exit();
}

// Fallback otomatis jika surat langsung dicetak tanpa penjadwalan (misal: Surat Izin Keluar / Surat Pernyataan)
if (empty($tanggal_surat)) {
    $tanggal_surat = date('Y-m-d');
}
if (empty($jam_surat)) {
    $jam_surat = date('H:i:s');
}

try {
    // Pastikan koneksi database PDO ($conn) tersedia
    if (isset($conn) && $conn !== null) {
        // Ambil nama kolom asli 'dibuat_oleh' sesuai dengan skema tabel log_surat di setup.php
        $stmt = $conn->prepare("
            INSERT INTO log_surat (student_id, tipe_surat, tanggal_surat, jam_surat, dibuat_oleh, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $student_id, 
            $tipe_surat, 
            $tanggal_surat, 
            $jam_surat, 
            $dibuat_oleh
        ]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => '✅ Log pencetakan berkas berhasil diarsipkan ke dalam sistem.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => '⚠️ Koneksi database tidak tersedia.'
        ]);
    }
} catch (PDOException $e) {
    // Tangkap kesalahan query database dan kirimkan sebagai pesan error JSON yang informatif
    echo json_encode([
        'status' => 'error', 
        'message' => '❌ Gagal mengarsipkan log ke database: ' . $e->getMessage()
    ]);
}
exit();