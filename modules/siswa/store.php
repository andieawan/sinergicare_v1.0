<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn !== null) {
    $nisn     = trim($_POST['nisn'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $class_id = (int)($_POST['class_id'] ?? 0);

    if (empty($nisn) || empty($nama) || $class_id <= 0) {
        setFlash('error', '⚠️ Gagal! NISN, Nama, dan Kelas wajib diisi secara valid.');
        header("Location: /pages/admin.php");
        exit();
    }

    try {
        // Validasi Duplikasi: Cek apakah NISN sudah terpakai oleh siswa lain
        $stmt_check = $conn->prepare("SELECT id FROM students WHERE nisn = ? LIMIT 1");
        $stmt_check->execute([$nisn]);
        if ($stmt_check->fetch()) {
            setFlash('error', "⚠️ Gagal! Siswa dengan NISN {$nisn} sudah terdaftar di sistem.");
            header("Location: /pages/admin.php");
            exit();
        }

        // Menyisipkan data siswa baru dengan status awal Zona Hijau (Aman)
        $stmt_insert = $conn->prepare("
            INSERT INTO students (nisn, nama, class_id, status_warna, level_eskalasi, status_sp, is_probation) 
            VALUES (?, ?, ?, 'hijau', 'teguran', 'tidak_ada', 0)
        ");
        $stmt_insert->execute([$nisn, $nama, $class_id]);

        setFlash('success', "✨ Berhasil menambahkan profil siswa baru atas nama: {$nama}.");
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal menyimpan data siswa: ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Metode pengiriman data tidak sah!');
}

header("Location: /pages/admin.php");
exit();