<?php
/**
 * Menghitung ulang status warna radar dan level eskalasi siswa berdasarkan insiden terakhir
 */
function hitungUlangRadarSiswa(PDO $conn, int $student_id): void {
    $stmt = $conn->prepare(
        "SELECT vc.bobot_risiko FROM incidents i
         JOIN violation_categories vc ON i.category_id = vc.id
         WHERE i.student_id = ?
         ORDER BY FIELD(vc.bobot_risiko, 'berat', 'sedang', 'ringan') LIMIT 1"
    );
    $stmt->execute([$student_id]);
    $res   = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $warna = 'hijau'; 
    $level = 'teguran';
    
    if ($res) {
        if ($res['bobot_risiko'] === 'berat')  { $warna = 'merah';  $level = 'skorsing_drop'; }
        if ($res['bobot_risiko'] === 'sedang') { $warna = 'kuning'; $level = 'konseling'; }
    }
    
    $conn->prepare("UPDATE students SET status_warna = ?, level_eskalasi = ? WHERE id = ?")
         ->execute([$warna, $level, $student_id]);
}

/**
 * Mengubah format tanggal (YYYY-MM-DD) menjadi format penanggalan resmi Indonesia
 */
function formatTanggalIndo(string $tanggal): string {
    if (empty($tanggal)) return '........................';
    
    $hari  = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                 
    $ts = strtotime($tanggal);
    return $hari[date('N', $ts)] . ', ' . date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Mendapatkan class Tailwind CSS untuk komponen badge warna berdasarkan status radar
 */
function getStatusBadgeClass(string $status): string {
    return match($status) {
        'merah'  => 'bg-rose-50 text-rose-600 border-rose-100',
        'kuning' => 'bg-amber-50 text-amber-600 border-amber-100',
        default  => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    };
}

/**
 * Pemetaan slug role di database menjadi label nama resmi untuk antarmuka pengguna
 */
function getRoleLabel(string $role): string {
    $labels = [
        'super_admin'    => 'Super Admin',
        'admin'          => 'Administrator',
        'bk'             => 'Guru BK',
        'guru'           => 'Guru',
        'waka_kesiswaan' => 'Waka Kesiswaan',
        'kepala_jurusan' => 'Kepala Jurusan',
        'yayasan'        => 'Yayasan',
    ];
    return $labels[$role] ?? ucwords(str_replace('_', ' ', $role));
}