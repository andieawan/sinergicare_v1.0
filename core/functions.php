<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * core/functions.php
 *
 * PERBAIKAN H5: hitungUlangRadarSiswa() sebelumnya hanya mengambil bobot TERPARAH
 * dengan LIMIT 1 — artinya 1 pelanggaran berat langsung zona merah tanpa melihat
 * total akumulasi. Logika baru menggunakan sistem skor tertimbang:
 * - ringan  = 1 poin per kejadian
 * - sedang  = 3 poin per kejadian
 * - berat   = 10 poin per kejadian
 *
 * Threshold zona:
 * - < 3 poin  → hijau  (teguran)
 * - 3–9 poin  → kuning (konseling)
 * - ≥ 10 poin → merah  (skorsing_drop)
 *
 * Ini memungkinkan 1 pelanggaran ringan tetap di zona hijau,
 * 3 pelanggaran ringan baru masuk kuning, dan 1 pelanggaran berat langsung merah
 * (karena 10 poin ≥ threshold merah — ini masih logis untuk pelanggaran berat).
 * Sesuaikan nilai konstanta BOBOT_* dan threshold jika kebijakan sekolah berbeda.
 */

/**
 * ============================================================================
 * KONSTANTA BOBOT & THRESHOLD RADAR
 * ============================================================================
 * CATATAN KEBIJAKAN SEKOLAH:
 * Nilai `BOBOT_BERAT` sengaja disamakan dengan `THRESHOLD_MERAH` (bernilai 10).
 * Hal ini bersifat INTENSIONAL agar siswa yang melakukan minimal 1 pelanggaran 
 * berat langsung otomatis masuk ke dalam ZONA MERAH tanpa harus menunggu 
 * akumulasi dari pelanggaran lainnya.
 */
const BOBOT_RINGAN     = 1;
const BOBOT_SEDANG     = 3;
const BOBOT_BERAT      = 10;
const THRESHOLD_KUNING = 3;   // skor >= ini → kuning
const THRESHOLD_MERAH  = 10;  // skor >= ini → merah

function hitungUlangRadarSiswa(PDO $conn, int $student_id): void {
    // Hitung skor akumulatif berdasarkan semua insiden siswa (bukan hanya terparah)
    $stmt = $conn->prepare("
        SELECT vc.bobot_risiko, COUNT(*) AS jumlah
        FROM incidents i
        JOIN violation_categories vc ON i.category_id = vc.id
        WHERE i.student_id = ?
        GROUP BY vc.bobot_risiko
    ");
    $stmt->execute([$student_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_skor = 0;
    foreach ($rows as $row) {
        $bobot = match($row['bobot_risiko']) {
            'berat'  => BOBOT_BERAT,
            'sedang' => BOBOT_SEDANG,
            default  => BOBOT_RINGAN,
        };
        $total_skor += $bobot * (int)$row['jumlah'];
    }

    // Tentukan zona berdasarkan total skor akumulatif
    if ($total_skor >= THRESHOLD_MERAH) {
        $warna = 'merah';
        $level = 'skorsing_drop';
    } elseif ($total_skor >= THRESHOLD_KUNING) {
        $warna = 'kuning';
        $level = 'konseling';
    } else {
        $warna = 'hijau';
        $level = 'teguran';
    }

    $conn->prepare("UPDATE students SET status_warna = ?, level_eskalasi = ? WHERE id = ?")
         ->execute([$warna, $level, $student_id]);
}

/**
 * Mengubah format tanggal (YYYY-MM-DD) menjadi format penanggalan resmi Indonesia.
 */
function formatTanggalIndo(string $tanggal): string {
    if (empty($tanggal)) return '........................';

    $hari  = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $ts = strtotime($tanggal);
    if ($ts === false) return '........................';

    return $hari[date('N', $ts)] . ', ' . date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Mendapatkan class Tailwind CSS untuk badge warna berdasarkan status radar.
 */
function getStatusBadgeClass(string $status): string {
    return match($status) {
        'merah'  => 'bg-rose-50 text-rose-600 border-rose-100',
        'kuning' => 'bg-amber-50 text-amber-600 border-amber-100',
        default  => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    };
}

/**
 * Pemetaan slug role ke label nama resmi untuk antarmuka pengguna.
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

/**
 * Generate / get CSRF token in session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token using constant-time comparison.
 */
function csrf_validate(?string $token): bool {
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }
    if (!is_string($token) || $token === '') {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
