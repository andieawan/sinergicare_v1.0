<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

if (isset($_GET['id']) && isset($conn) && $conn !== null) {
    $id = (int)$_GET['id'];

    try {
        // Cek eksistensi sebelum menghapus
        $stmt_check = $conn->prepare("SELECT nama FROM students WHERE id = ? LIMIT 1");
        $stmt_check->execute([$id]);
        $siswa = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$siswa) {
            setFlash('error', '⚠️ Profil siswa tidak dijumpai atau telah terhapus.');
            header("Location: /pages/admin.php");
            exit();
        }

        // Eksekusi penghapusan records (Aturan integritas DB akan mengikuti set fk cascade/restrict yang ada)
        $stmt_del = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt_del->execute([$id]);

        setFlash('success', "✨ Berhasil menghapus seluruh data siswa: {$siswa['nama']}.");
    } catch (PDOException $e) {
        setFlash('error', '⚠️ Gagal menghapus data siswa (Kemungkinan data terikat dengan jurnal insiden): ' . $e->getMessage());
    }
} else {
    setFlash('error', '⚠️ Parameter ID Siswa tidak valid!');
}

header("Location: /pages/admin.php");
exit();