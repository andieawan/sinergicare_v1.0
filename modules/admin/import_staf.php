<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
require_once __DIR__ . '/../../vendor/autoload.php';

requireLogin();
requireRole(['admin', 'super_admin']);

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/admin.php');
    exit();
}

// 1. Validasi File Upload
if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    setFlash('error', 'Gagal mengunggah file. Pastikan file dipilih.');
    header('Location: /pages/admin.php');
    exit();
}

$fileTmpPath = $_FILES['file_excel']['tmp_name'];
$fileName    = $_FILES['file_excel']['name'];
$fileSize    = $_FILES['file_excel']['size'];
$fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validasi Ekstensi (.xlsx)
if ($fileExt !== 'xlsx') {
    setFlash('error', 'Format file wajib .xlsx');
    header('Location: /pages/admin.php');
    exit();
}

// Validasi Ukuran Maksimal 5MB
if ($fileSize > 5 * 1024 * 1024) {
    setFlash('error', 'Ukuran file maksimal 5MB.');
    header('Location: /pages/admin.php');
    exit();
}

try {
    // 2. Baca File Excel
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestDataRow();

    $berhasil = 0;
    $gagal    = 0;
    $logGagal = [];

    $allowedRoles = ['admin', 'bk', 'guru', 'waka_kesiswaan', 'kepala_jurusan', 'yayasan'];

    // Siapkan Prepared Statements untuk mengecek duplikasi
    $stmtCheckUsername = $conn->prepare("SELECT id FROM staf_sekolah WHERE username = ?");
    $stmtCheckEmail    = $conn->prepare("SELECT id FROM staf_sekolah WHERE email = ?");
    
    // Siapkan Prepared Statement untuk Insert
    $stmtInsert = $conn->prepare("
        INSERT INTO staf_sekolah (nama, email, username, password, roles, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    // 3. Proses Baris per Baris (Mulai dari baris 2 karena baris 1 adalah header)
    for ($row = 2; $row <= $highestRow; $row++) {
        $nama     = trim($sheet->getCell('A' . $row)->getValue() ?? '');
        $email    = trim($sheet->getCell('B' . $row)->getValue() ?? '');
        $username = trim($sheet->getCell('C' . $row)->getValue() ?? '');
        $password = trim($sheet->getCell('D' . $row)->getValue() ?? '');
        $roles    = trim($sheet->getCell('E' . $row)->getValue() ?? '');

        // Abaikan baris yang benar-benar kosong atau baris catatan template
        if (empty($nama) && empty($username) && empty($password)) continue;
        if (strpos(strtolower($nama), 'catatan:') === 0) continue; 

        // Validasi Kelengkapan Data Wajib
        if (empty($nama) || empty($username) || empty($password) || empty($roles)) {
            $gagal++;
            $logGagal[] = "Baris $row: Data wajib tidak lengkap.";
            continue;
        }

        // Validasi Role
        if (!in_array($roles, $allowedRoles)) {
            $gagal++;
            $logGagal[] = "Baris $row: Role '$roles' tidak valid.";
            continue;
        }

        // Cek Duplikasi Username
        $stmtCheckUsername->execute([$username]);
        if ($stmtCheckUsername->fetch()) {
            $gagal++;
            $logGagal[] = "Baris $row: Username '$username' sudah terdaftar.";
            continue;
        }

        // Cek Duplikasi Email (jika diisi)
        if (!empty($email)) {
            $stmtCheckEmail->execute([$email]);
            if ($stmtCheckEmail->fetch()) {
                $gagal++;
                $logGagal[] = "Baris $row: Email '$email' sudah terdaftar.";
                continue;
            }
        }

        // Enkripsi Password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Eksekusi Insert
        try {
            $stmtInsert->execute([
                $nama, 
                empty($email) ? null : $email, 
                $username, 
                $hashedPassword, 
                $roles
            ]);
            $berhasil++;
        } catch (PDOException $e) {
            $gagal++;
            $logGagal[] = "Baris $row: Gagal menyimpan ke DB.";
        }
    }

    // 4. Kesimpulan dan Flash Message
    if ($berhasil > 0 && $gagal == 0) {
        setFlash('success', "Import sukses! $berhasil data staf berhasil ditambahkan.");
    } elseif ($berhasil > 0 && $gagal > 0) {
        $errText = implode(' | ', array_slice($logGagal, 0, 2)) . ($gagal > 2 ? ' ...dll' : '');
        setFlash('success', "Import parsial: $berhasil berhasil, $gagal dilewati. ($errText)");
    } else {
        $errText = implode(' | ', array_slice($logGagal, 0, 2)) . ($gagal > 2 ? ' ...dll' : '');
        setFlash('error', "Import gagal! Semua baris bermasalah. ($errText)");
    }

} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    setFlash('error', 'Gagal membaca format file Excel.');
} catch (Exception $e) {
    setFlash('error', 'Terjadi kesalahan sistem saat memproses.');
}

header('Location: /pages/admin.php');
exit();