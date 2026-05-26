<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
// Tambahkan core/flash.php agar fungsi setFlash() dapat digunakan
require_once __DIR__ . '/../../core/flash.php';

requireLogin();
requireRole(['super_admin', 'admin']);

// PERBAIKAN SEKURITAS: Mengganti die() mentah dengan penanganan anggun via session flash
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    setFlash('error', '⚠️ Gagal mengekspor data! Fitur integrasi Excel belum dikonfigurasi sepenuhnya di server (Pustaka vendor eksternal tidak ditemukan).');
    header("Location: /pages/admin.php");
    exit();
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (isset($conn) && $conn !== null) {
    try {
        $stmt = $conn->query("
            SELECT s.nisn, s.nama, c.nama_kelas, s.status_warna, s.level_eskalasi, s.status_sp, s.is_probation, s.probation_end
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            ORDER BY c.nama_kelas ASC, s.nama ASC
        ");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa Master');

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
                'startColor' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension('1')->setRowHeight(28);

        $rowNum = 2;
        foreach ($students as $s) {
            $probation_status  = $s['is_probation'] ? 'Aktif' : 'Tidak Aktif';
            $probation_end_val = ($s['is_probation'] && $s['probation_end'])
                ? date('d-m-Y', strtotime($s['probation_end']))
                : '-';

            // BUG FIX 6: status_sp kini VARCHAR — bandingkan ke string 'tidak_ada'
            $status_sp_label = ($s['status_sp'] !== 'tidak_ada')
                ? strtoupper(str_replace('_', ' ', $s['status_sp']))
                : 'Tidak Ada';

            $sheet->setCellValue('A' . $rowNum, $s['nisn']);
            $sheet->setCellValue('B' . $rowNum, $s['nama']);
            $sheet->setCellValue('C' . $rowNum, $s['nama_kelas'] ?? 'Tanpa Kelas');
            $sheet->setCellValue('D' . $rowNum, ucfirst($s['status_warna']));
            $sheet->setCellValue('E' . $rowNum, ucfirst(str_replace('_', ' ', $s['level_eskalasi'])));
            $sheet->setCellValue('F' . $rowNum, $status_sp_label);
            $sheet->setCellValue('G' . $rowNum, $probation_status);
            $sheet->setCellValue('H' . $rowNum, $probation_end_val);

            if ($rowNum % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':' . $highestColumn . $rowNum)->getFill()->applyFromArray([
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ]);
            }

            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;
        }

        $totalRowsData = $rowNum - 1;
        $fullTableRange = 'A1:' . $highestColumn . $totalRowsData;

        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()->getColor()->setRGB('E2E8F0');

        $sheet->getStyle('A2:A' . $totalRowsData)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:H' . $totalRowsData)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:B' . $totalRowsData)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bersihkan output buffer agar tidak ada kebocoran yang merusak file Excel
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="SinergiCare_MasterSiswa_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();

    } catch (Exception $e) {
        // PERBAIKAN: Ganti die() teknis dengan redirect + flash error
        setFlash('error', '⚠️ Sistem mendeteksi kegagalan saat menyusun lembar kerja Excel: ' . $e->getMessage());
        header("Location: /pages/admin.php");
        exit();
    }
} else {
    // PERBAIKAN: Penanganan ketika objek koneksi bermasalah
    setFlash('error', '⚠️ Koneksi ke basis data utama SinergiCare sedang tidak tersedia.');
    header("Location: /pages/admin.php");
    exit();
}