<?php
// /prints/cetak_pernyataan.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

// Proteksi halaman cetak
requireLogin();

if (!isset($_GET['student_id'])) {
    die("Data siswa tidak valid.");
}
$student_id = (int)$_GET['student_id'];

$stmt = $conn->prepare("SELECT s.*, c.nama_kelas FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = :id");
$stmt->execute(['id' => $student_id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    die("Siswa tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Kedisiplinan - <?php echo htmlspecialchars($siswa['nama']); ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 14px; padding: 40px; line-height: 1.5; color: #000; }
        .kop { display: flex; align-items: center; justify-content: center; border-bottom: 4px double #000; padding-bottom: 12px; margin-bottom: 30px; gap: 20px; }
        .kop-text { text-align: center; flex-grow: 1; }
        .kop h2 { margin: 0; font-size: 18px; text-transform: uppercase; line-height: 1.3; }
        .kop p { margin: 5px 0 0 0; font-size: 12px; }
        .logo-img { max-height: 85px; max-width: 85px; object-fit: contain; }
        .spacer { width: 85px; }
        
        .judul-surat { text-align: center; margin-bottom: 25px; text-transform: uppercase; text-decoration: underline; font-weight: bold; font-size: 16px; }
        .content { margin-bottom: 30px; text-align: justify; }
        .table-identitas { margin: 15px 40px; width: 80%; }
        .table-identitas td { padding: 4px 10px; vertical-align: top; }
        .table-identitas td:first-child { width: 30%; }
        
        .ttd-container { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .ttd-box { width: 200px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="kop">
        <?php if (file_exists(__DIR__ . '/../uploads/logo.png')): ?>
            <img src="/uploads/logo.png" class="logo-img" alt="Logo Sekolah">
        <?php endif; ?>
        <div class="kop-text">
            <h2>PEMERINTAH PROVINSI / YAYASAN PENDIDIKAN<br>SMK PUSAT KEUNGGULAN SINERGICARE</h2>
            <p>Jl. Jenderal Sudirman No. 123, Indonesia | Telp: (021) 555-1234 | Email: info@smk.sch.id</p>
        </div>
        <?php if (file_exists(__DIR__ . '/../uploads/logo.png')): ?>
            <div class="spacer"></div>
        <?php endif; ?>
    </div>

    <div class="judul-surat">Surat Pernyataan Kedisiplinan Siswa</div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        
        <table class="table-identitas">
            <tr><td>Nama Lengkap</td><td>: <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong></td></tr>
            <tr><td>NISN</td><td>: <?php echo htmlspecialchars($siswa['nisn']); ?></td></tr>
            <tr><td>Kelas</td><td>: <?php echo htmlspecialchars($siswa['nama_kelas']); ?></td></tr>
            <tr><td>Status Radar</td><td>: <span style="text-transform: uppercase; font-weight: bold;"><?php echo htmlspecialchars($siswa['status_warna']); ?></span></td></tr>
        </table>

        <p>Dengan kesadaran penuh dan tanpa paksaan dari pihak mana pun, saya menyatakan bahwa:</p>
        <ol style="margin-top: 5px;">
            <li>Mengakui telah melakukan pelanggaran terhadap tata tertib kedisiplinan SMK SinergiCare.</li>
            <li>Berjanji akan memperbaiki sikap dan perilaku di lingkungan sekolah maupun di luar sekolah.</li>
            <li>Bersedia mematuhi segala peraturan dan tata tertib yang berlaku di SMK SinergiCare.</li>
            <li>Apabila di kemudian hari saya mengulangi pelanggaran yang sama atau melakukan pelanggaran tata tertib lainnya, saya bersedia menerima sanksi yang lebih berat, termasuk dikembalikan kepada Orang Tua / Wali (Dikeluarkan dari Sekolah).</li>
        </ol>

        <p>Demikian surat pernyataan ini saya buat dengan sesungguhnya untuk dapat dipertanggungjawabkan.</p>
    </div>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Mengetahui,<br>Orang Tua / Wali Siswa</p>
            <br><br><br><br>
            <p>__________________________</p>
        </div>
        <div class="ttd-box">
            <p>Mengetahui,<br>Guru Pembimbing BK</p>
            <br><br><br><br>
            <p>__________________________</p>
        </div>
        <div class="ttd-box">
            <p>Jember, <?php echo formatTanggalIndo(date('Y-m-d')); ?><br>Siswa yang membuat pernyataan</p>
            <br><br><br><br>
            <p><strong><?php echo htmlspecialchars($siswa['nama']); ?></strong></p>
        </div>
    </div>

</body>
</html>