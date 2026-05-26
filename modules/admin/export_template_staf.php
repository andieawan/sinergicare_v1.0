<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';

// Pastikan composer autoload sudah dipanggil
require_once __DIR__ . '/../../vendor/autoload.php';

requireLogin();
// Hanya admin/super_admin yang berhak mengelola staf
requireRole(['admin', 'super_admin']);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 1. Set Header Kolom
$headers = ['nama', 'email', 'username', 'password', 'roles'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Styling Header (Bold & Center)
$headerStyle = $sheet->getStyle('A1:E1');
$headerStyle->getFont()->setBold(true);
$headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 2. Berikan 1 baris dummy data sebagai contoh
$sheet->setCellValue('A2', 'Budi Santoso');
$sheet->setCellValue('B2', 'budi.santoso@sekolah.sch.id');
$sheet->setCellValue('C2', 'budiguru');
$sheet->setCellValue('D2', 'Password123');
$sheet->setCellValue('E2', 'guru');

// Tambahkan catatan/hint di baris ke-3 
$sheet->setCellValue('A3', 'Catatan: Baris contoh ini (baris 2) harap dihapus sebelum di-import.');
$sheet->mergeCells('A3:E3');
$sheet->getStyle('A3')->getFont()->setItalic(true)->getColor()->setARGB('FF888888');

// 3. Auto-size kolom agar rapi saat dibuka
foreach (range('A', 'E') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// =========================================================
// FIX ERROR CORRUPT: Bersihkan output buffer dari kebocoran
// =========================================================
if (ob_get_length()) {
    ob_end_clean();
}

// 4. Output Stream langsung ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Import_Staf.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();