<?php
/**
 * config/config.php
 * * Berkas Konfigurasi Utama Sistem Informasi Manajemen Kedisiplinan Siswa
 * "SinergiCare Sistem Radar Karakter"
 */

// 1. Pengaturan Zona Waktu Resmi (WIB)
// Sangat krusial agar kalkulasi batas waktu edit mandiri insiden (30 menit) 
// sinkron antara waktu server PHP dan basis data, menghindari bias zona waktu.
date_default_timezone_set('Asia/Jakarta');

// 2. Definisi Konstanta Global Base URL Aplikasi
// Digunakan secara terpusat untuk memuat template ekspor, berkas aset, dan tautan navigasi.
// Sesuaikan dengan domain atau nama local virtual host yang Anda gunakan (contoh: http://sinergicare.test).
define('WEB_BASE', 'http://sinergicare.test');

// Variabel backward compatibility (kompatibilitas mundur) 
// Menjamin komponen kode lama yang masih memanggil variabel $web_base tidak mengalami error notice.
$web_base = WEB_BASE;

// 3. Konfigurasi Kredensial Basis Data (MySQL / MariaDB)
define('DB_HOST', 'localhost');
define('DB_NAME', 'sinergicare'); // Ganti dengan nama database SinergiCare Anda
define('DB_USER', 'root');        // Ganti dengan username database server Anda
define('DB_PASS', '');            // Ganti dengan password database server Anda

// 4. Inisialisasi Variabel Koneksi Utama
$conn = null;

try {
    // Menyusun Data Source Name (DSN) dengan setelan charset utf8mb4 agar mendukung karakter unicode khusus/emotikon
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Konfigurasi atribut keamanan dan optimasi PDO Driver
    $options = [
        // Mengaktifkan mode exception agar setiap kegagalan query melempar PDOException yang dapat ditangkap oleh blok try/catch aplikasi
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        
        // Mengatur default pengembalian data kueri otomatis sebagai Array Asosiatif
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        // Menonaktifkan emulasi prepared statements agar native driver MySQL yang bekerja (mencegah SQL Injection & menjaga tipe data asli)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Instansiasi objek PDO ke variabel koneksi global $conn
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Mencatat kegagalan koneksi ke dalam file log internal server
    error_log("SinergiCare DB Connection Failure: " . $e->getMessage());
    
    // Memastikan variabel $conn tetap bernilai null agar dapat divalidasi secara anggun 
    // oleh file kontroler pembawa kueri melalui kondisi (if ($conn === null))
    $conn = null;
    
    // CATATAN PENGEMBANGAN: 
    // Jika Anda sedang melakukan setup awal/debugging di lokal komputer, 
    // Anda bisa mengaktifkan baris die() di bawah ini untuk melihat detail error:
    // die("Koneksi ke database SinergiCare gagal terhubung: " . $e->getMessage());
}