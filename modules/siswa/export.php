<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';

// Proteksi Keamanan Sesi - Hanya Otoritas Administrator & Super Admin
requireLogin();
requireRole(['super_admin', 'admin']);

// Memanggil file Autoload Vendor Composer guna memuat pustaka PhpSpreadsheet
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    die("Pustaka PhpSpreadsheet belum terinstal di server. Silakan jalankan perintah 'composer require phpoffice/phpspreadsheet' terlebih dahulu.");
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (isset($conn) && $conn !== null) {
    try {
        // 1. Mengambil data seluruh entitas siswa beserta relasi kelasnya
        $stmt = $conn->query("
            SELECT s.nisn, s.nama, c.nama_kelas, s.status_warna, s.level_eskalasi, s.status_sp, s.is_probation, s.probation_end
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            ORDER BY c.nama_kelas ASC, s.nama ASC
        ");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Inisialisasi Objek Lembar Kerja Spreadsheet Baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa Master');

        // 3. Susun Baris Judul Header Kolom (Baris 1)
        $headers = [
            'NISN', 
            'Nama Lengkap Siswa', 
            'Kelas', 
            'Status Warna Radar', 
            'Level Eskalasi Kasus', 
            'Status Surat Peringatan', 
            'Masa Probation', 
            'Tanggal Batas Probation'
        ];
        
        $columnIndex = 'A';
        foreach ($headers as $headerText) {
            $sheet->setCellValue($columnIndex . '1', $headerText);
            $columnIndex++;
        }

        // --- STYLING HEADER BARIS UTAMA ---
        $highestColumn = $sheet->getHighestColumn();
        $headerRange = 'A1:' . $highestColumn . '1';
        
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'], // Slate 900 (Selaras dengan Identitas Gelap Tema SinergiCare)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Atur tinggi baris header agar terlihat longgar dan profesional
        $sheet->getRowDimension('1')->setRowHeight(28);

        // 4. Pengisian Data Baris Dinamis (Baris 2 s/d Selesai)
        $rowNum = 2;
        foreach ($students as $s) {
            $probation_status = $s['is_probation'] ? 'Aktif' : 'Tidak Aktif';
            $probation_end_val = ($s['is_probation'] && $s['probation_end']) ? date('d-m-Y', strtotime($s['probation_end'])) : '-';

            $sheet->setCellValue('A' . $rowNum, $s['nisn']);
            $sheet->setCellValue('B' . $rowNum, $s['nama']);
            $sheet->setCellValue('C' . $rowNum, $s['nama_kelas'] ?? 'Tanpa Kelas');
            $sheet->setCellValue('D' . $rowNum, ucfirst($s['status_warna']));
            $sheet->setCellValue('E' . $rowNum, ucfirst(str_replace('_', ' ', $s['level_eskalasi'])));
            $sheet->setCellValue('F' . $rowNum, $s['status_sp'] !== 'tidak_ada' ? strtoupper(str_replace('_', ' ', $s['status_sp'])) : 'Tidak Ada');
            $sheet->setCellValue('G' . $rowNum, $probation_status);
            $sheet->setCellValue('H' . $rowNum, $probation_end_val);

            // Menerapkan teknik Zebra Striping ringan untuk baris genap demi memudahkan mata membaca data
            if ($rowNum % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':' . $highestColumn . $rowNum)->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'], // Slate 50
                ]);
            }

            // Atur tinggi baris konten data
            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;
        }

        // --- STYLING GLOBAL DATA CELL ---
        $totalRowsData = $rowNum - 1;
        $fullTableRange = 'A1:' . $highestColumn . $totalRowsData;
        
        // Pasang garis grid/border tipis berwarna abu-abu soft
        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()->getColor()->setRGB('E2E8F0'); // Slate 200

        // Format perataan teks (Alignment) kolom
        $sheet->getStyle('A2:A' . $totalRowsData)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:H' . $totalRowsData)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:B' . $totalRowsData)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Otomatisasi penyesuaian lebar kolom (Auto-Fit Column Width) sesuai panjang data
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 5. Mengirimkan Header HTTP Stream Dokumen ke Browser untuk Trigger Proses Unduh Langsung
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="SinergiCare_MasterSiswa_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1'); // Fallback akselerasi cache internet explorer

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();

    } catch (Exception $e) {
        die("Sistem mendeteksi kegagalan ekspor dokumen kerja Excel: " . $e->getMessage());
    }
} else {
    die("Koneksi ke basis data utama SinergiCare tidak tersedia.");
}