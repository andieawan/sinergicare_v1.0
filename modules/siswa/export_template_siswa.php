<?php
require_once '../../config/config.php';
require_once '../../core/auth.php';

// Pastikan composer autoload sudah dipanggil
require_once '../../vendor/autoload.php';

requireLogin();
// Hanya admin/super_admin yang berhak mengelola master data siswa
requireRole(['admin', 'super_admin']);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 1. Set Header Kolom
$headers = ['nisn', 'nama_siswa', 'nama_kelas'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Styling Header (Bold & Center)
$headerStyle = $sheet->getStyle('A1:C1');
$headerStyle->getFont()->setBold(true);
$headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 2. Berikan 1 baris dummy data sebagai contoh
// NISN di-set explicit sebagai STRING agar angka 0 di depan tidak terhapus
$sheet->setCellValueExplicit('A2', '0012345678', DataType::TYPE_STRING);
$sheet->setCellValue('B2', 'Andi Prasetyo');
$sheet->setCellValue('C2', 'XII DKV 1');

// Tambahkan catatan/hint di baris ke-3
$sheet->setCellValue('A3', 'Catatan: Baris contoh ini (baris 2) harap dihapus sebelum di-import.');
$sheet->mergeCells('A3:C3');
$sheet->getStyle('A3')->getFont()->setItalic(true)->getColor()->setARGB('FF888888');

// 3. Auto-size kolom agar rapi saat dibuka
foreach (range('A', 'C') as $columnID) {
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
header('Content-Disposition: attachment;filename="Template_Import_Siswa.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();