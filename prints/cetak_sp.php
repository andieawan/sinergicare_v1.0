<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
// BUG FIX 3: tambahkan functions.php — formatTanggalIndo() dipakai di halaman ini
// tapi sebelumnya tidak pernah di-include sehingga fatal error saat cetak
require_once __DIR__ . '/../core/functions.php';

requireLogin();

$sp_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($sp_id <= 0 || !isset($conn) || $conn === null) {
    die("<h3>⚠️ Akses Gagal</h3>Parameter ID Surat Peringatan tidak valid atau tidak disertakan.");
}

try {
    $stmt = $conn->prepare("
        SELECT sp.*, s.nama AS nama_siswa, s.nisn, c.nama_kelas, st.nama AS nama_pejabat
        FROM sp_records sp
        JOIN students s ON sp.student_id = s.id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN staf_sekolah st ON sp.diterbitkan_oleh = st.id
        WHERE sp.id = ? LIMIT 1
    ");
    $stmt->execute([$sp_id]);
    $sp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sp) {
        die("<h3>⚠️ Berkas Tidak Ditemukan</h3>Dokumen arsip Surat Peringatan tidak tercatat dalam database.");
    }

    if ((int)$sp['is_approved'] !== 1) {
        die("<h3>⚠️ Cetakan Terkunci</h3>Dokumen Surat Peringatan ini belum mendapatkan persetujuan resmi (Approval) dari Kesiswaan sehingga tidak sah untuk dicetak.");
    }

} catch (PDOException $e) {
    die("<h3>⚠️ Kendala Sistem</h3>Gagal memuat dokumen cetak akibat gangguan database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak <?php echo strtoupper(str_replace('_', ' ', $sp['tingkat_sp'])); ?> - <?php echo htmlspecialchars($sp['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 750px;
            margin: 0 auto;
            padding: 40px 30px;
            box-sizing: border-box;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 25px;
            text-align: center;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: normal;
            letter-spacing: 0.5px;
        }
        .kop-surat h1 {
            margin: 2px 0;
            font-size: 20px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .kop-surat p {
            margin: 2px 0;
            font-size: 11px;
            font-style: italic;
        }
        .judul-surat {
            text-align: center;
            margin-bottom: 30px;
        }
        .judul-surat h3 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .judul-surat p {
            margin: 3px 0;
            font-size: 13px;
        }
        p.narasi {
            text-align: justify;
            text-indent: 40px;
            margin: 14px 0;
            font-size: 14px;
        }
        .table-identitas {
            width: 85%;
            margin: 15px auto;
            border-collapse: collapse;
            font-size: 14px;
        }
        .table-identitas td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .table-identitas td:first-child {
            width: 32%;
        }
        .table-identitas td:nth-child(2) {
            width: 4%;
        }
        .box-alasan {
            border: 1px dashed #333;
            padding: 12px 18px;
            margin: 15px auto;
            width: 85%;
            font-style: italic;
            background-color: #fafafa;
            font-size: 13px;
            text-align: justify;
            line-height: 1.5;
            box-sizing: border-box;
        }
        .area-ttd {
            width: 100%;
            margin-top: 60px;
            font-size: 14px;
        }
        .table-ttd {
            width: 100%;
            border-collapse: collapse;
        }
        .table-ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .space-kosong-ttd {
            height: 85px;
        }
        .nama-pejabat-ttd {
            font-weight: bold;
            text-decoration: underline;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .container { max-width: 100%; padding: 15px; }
            .box-alasan { background-color: transparent !important; border: 1px solid #000; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="kop-surat">
        <h2>Yayasan Sinergi Pendidikan Indonesia</h2>
        <h1>SMK SINERGI PUSAT KEUNGGULAN</h1>
        <p>Jl. Pendidikan Karakter No. 45, Kota Vocasi — Telp: (021) 555-1234 | Email: info@smksinergi.sch.id</p>
    </div>

    <div class="judul-surat">
        <h3><?php echo strtoupper(str_replace('_', ' ', $sp['tingkat_sp'])); ?></h3>
        <p>Nomor: SP/<?php echo date('Y/m/', strtotime($sp['created_at'])) . str_pad($sp['id'], 3, '0', STR_PAD_LEFT); ?></p>
    </div>

    <p class="narasi">Surat Peringatan resmi ini diterbitkan oleh pihak otoritas kedisiplinan sekolah dan diberikan kepada siswa yang teridentifikasi di bawah ini sebagai langkah penindakan korektif tegas serta pembinaan karakter atas akumulasi perilaku ketertiban yang terekam di sistem radar sekolah:</p>

    <table class="table-identitas">
        <tr>
            <td>Nama Lengkap Siswa</td>
            <td>:</td>
            <td><strong><?php echo htmlspecialchars($sp['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td><?php echo htmlspecialchars($sp['nisn'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td>Kelas / Keahlian</td>
            <td>:</td>
            <td><?php echo htmlspecialchars($sp['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    </table>

    <p class="narasi">Adapun yang menjadi landasan dasar hukum utama dan bahan pertimbangan diterbitkannya penindakan disiplin bertahap ini adalah dikarenakan siswa yang bersangkutan telah terbukti melakukan pelanggaran dengan deskripsi kronologi sebagai berikut:</p>

    <div class="box-alasan">
        <?php echo nl2br(htmlspecialchars($sp['alasan_sp'], ENT_QUOTES, 'UTF-8')); ?>
    </div>

    <p class="narasi">Sehubungan dengan ketetapan surat ini, maka terhitung sejak tanggal diterbitkannya dokumen, siswa dinyatakan berada dalam status <strong>Masa Probation (Uji Coba Perilaku) selama 30 hari kalender</strong>. Apabila di kemudian hari selama masa probation siswa kembali melakukan tindakan pelanggaran tata tertib, sekolah akan langsung menjatuhkan level eskalasi sangsi yang jauh lebih berat tanpa dispensasi.</p>

    <p class="narasi">Demikian berkas ketetapan surat peringatan ini dikeluarkan untuk dijadikan perhatian penuh, ditaati dengan penuh kesadaran, serta dipahami sebagai bahan evaluasi bersama bagi siswa maupun orang tua / wali murid.</p>

    <div class="area-ttd">
        <table class="table-ttd">
            <tr>
                <td>
                    <p>Orang Tua / Wali Murid,</p>
                    <div class="space-kosong-ttd"></div>
                    <p>....................................................</p>
                </td>
                <td>
                    <p>Kota Vocasi, <?php echo formatTanggalIndo(date('Y-m-d', strtotime($sp['created_at']))); ?></p>
                    <p>Waka Kesiswaan / Pejabat Berwenang,</p>
                    <div class="space-kosong-ttd"></div>
                    <p class="nama-pejabat-ttd"><?php echo htmlspecialchars($sp['nama_pejabat'] ?? 'Otoritas Kesiswaan', ENT_QUOTES, 'UTF-8'); ?></p>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => { window.print(); }, 400);
    });
</script>
</body>
</html>
