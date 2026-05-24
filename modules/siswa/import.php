<?php
require_once '../../config/config.php';
require_once '../../core/auth.php';
require_once '../../core/flash.php';
require_once '../../vendor/autoload.php';

requireLogin();
// Hanya admin/super_admin yang berhak mengelola data induk siswa
requireRole(['admin', 'super_admin']);

use PhpOffice\PhpSpreadsheet\IOFactory;

// Pastikan metode adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/admin.php');
    exit();
}

// 1. Validasi File Upload
if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    setFlash('error', 'Gagal mengunggah file. Pastikan file Excel telah dipilih.');
    header('Location: ../../pages/admin.php');
    exit();
}

$fileTmpPath = $_FILES['file_excel']['tmp_name'];
$fileName    = $_FILES['file_excel']['name'];
$fileSize    = $_FILES['file_excel']['size'];
$fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validasi Ekstensi file wajib .xlsx
if ($fileExt !== 'xlsx') {
    setFlash('error', 'Format file wajib .xlsx (Excel).');
    header('Location: ../../pages/admin.php');
    exit();
}

// Validasi ukuran maksimal 5MB
if ($fileSize > 5 * 1024 * 1024) {
    setFlash('error', 'Ukuran file maksimal 5MB.');
    header('Location: ../../pages/admin.php');
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

    // Siapkan statement untuk efisiensi
    $stmtCheckSiswa  = $conn->prepare("SELECT id FROM students WHERE nisn = ?");
    $stmtCheckKelas  = $conn->prepare("SELECT id FROM classes WHERE nama_kelas = ?");
    $stmtInsertKelas = $conn->prepare("INSERT INTO classes (nama_kelas) VALUES (?)");
    
    // Insert siswa (default warna hijau dan level teguran)
    $stmtInsertSiswa = $conn->prepare("
        INSERT INTO students (nisn, nama, class_id, status_warna, level_eskalasi) 
        VALUES (?, ?, ?, 'hijau', 'teguran')
    ");

    // 3. Proses Baris per Baris
    for ($row = 2; $row <= $highestRow; $row++) {
        // Ambil data dan cast ke string agar aman (terutama NISN yang mungkin diawali nol)
        // BUG FIX (BUG-09): Gunakan getFormattedValue() untuk NISN agar leading zero (0) tidak terpotong
        $nisn       = trim((string)($sheet->getCell('A' . $row)->getFormattedValue() ?? ''));
        $nama_siswa = trim((string)($sheet->getCell('B' . $row)->getValue() ?? ''));
        $nama_kelas = trim((string)($sheet->getCell('C' . $row)->getValue() ?? ''));

        // Skip baris kosong atau baris catatan
        if (empty($nisn) && empty($nama_siswa)) continue;
        if (strpos(strtolower($nisn), 'catatan:') === 0) continue; 

        // Validasi kelengkapan
        if (empty($nisn) || empty($nama_siswa) || empty($nama_kelas)) {
            $gagal++;
            $logGagal[] = "Baris $row: Data (NISN/Nama/Kelas) tidak lengkap.";
            continue;
        }

        // Cek duplikasi NISN
        $stmtCheckSiswa->execute([$nisn]);
        if ($stmtCheckSiswa->fetch()) {
            $gagal++;
            $logGagal[] = "Baris $row: NISN '$nisn' sudah terdaftar.";
            continue;
        }

        // Cari ID Kelas atau Buat Kelas Baru jika belum ada
        $stmtCheckKelas->execute([$nama_kelas]);
        $kelas = $stmtCheckKelas->fetch();
        
        if ($kelas) {
            $class_id = $kelas['id'];
        } else {
            $stmtInsertKelas->execute([$nama_kelas]);
            $class_id = $conn->lastInsertId(); // Ambil ID dari kelas yang baru saja dibuat
        }

        // Simpan Data Siswa
        try {
            $stmtInsertSiswa->execute([$nisn, $nama_siswa, $class_id]);
            $berhasil++;
        } catch (PDOException $e) {
            $gagal++;
            $logGagal[] = "Baris $row: Gagal menyimpan data siswa ke DB.";
        }
    }

    // 4. Set Flash Message sesuai hasil
    if ($berhasil > 0 && $gagal == 0) {
        setFlash('success', "Import sukses! $berhasil data siswa berhasil ditambahkan.");
    } elseif ($berhasil > 0 && $gagal > 0) {
        // Gabungkan log error max 2 pesan saja agar alert tidak kepanjangan
        $errText = implode(' | ', array_slice($logGagal, 0, 2)) . ($gagal > 2 ? ' ...dll' : '');
        setFlash('success', "Import parsial: $berhasil berhasil, $gagal dilewati. ($errText)");
    } else {
        $errText = implode(' | ', array_slice($logGagal, 0, 2)) . ($gagal > 2 ? ' ...dll' : '');
        setFlash('error', "Import gagal! Semua baris ditolak. ($errText)");
    }

} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    setFlash('error', 'Gagal membaca format file Excel. Pastikan file tidak rusak.');
} catch (Exception $e) {
    setFlash('error', 'Terjadi kesalahan sistem saat memproses file Excel.');
}

// Kembali ke halaman admin
header('Location: ../../pages/admin.php');
exit();