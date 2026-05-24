<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';

// Proteksi Sesi & Pembatasan Hak Akses Khusus Admin
requireLogin();
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_csv']) && $_FILES['file_csv']['error'] === 0) {
    $file_tmp = $_FILES['file_csv']['tmp_name'];
    
    // Membuka berkas unggahan CSV dengan mode read-only
    if (($handle = fopen($file_tmp, "r")) !== FALSE) {
        
        // Antisipasi konfigurasi delimiter eksplisit dari beberapa software spreadsheet (contoh sep=,)
        $first_line = fgets($handle);
        if (strpos($first_line, 'sep=') === false) { 
            rewind($handle); 
        }

        // Membaca baris pertama sebagai susunan header kolom
        $header = fgetcsv($handle, 1000, ",");
        
        if (!$header) {
            setFlash('error', '⚠️ Berkas CSV kosong atau tidak terbaca.');
            header("Location: /pages/admin.php");
            exit();
        }

        // Normalisasi teks header: ubah ke huruf kecil dan hilangkan spasi kosong di ujung teks
        $header = array_map('strtolower', array_map('trim', $header));
        
        // Memetakan indeks posisi kolom secara dinamis
        $idx_nisn  = array_search('nisn', $header);
        $idx_nama  = array_search('nama', $header);
        $idx_kelas = array_search('kelas', $header);

        // Validasi ketersediaan kolom wajib
        if ($idx_nisn === FALSE || $idx_nama === FALSE || $idx_kelas === FALSE) {
            fclose($handle);
            setFlash('error', '⚠️ Format header CSV tidak valid! Pastikan struktur kolom baris pertama berisi nama: nisn, nama, kelas');
            header("Location: /pages/admin.php");
            exit();
        }

        $success_count = 0;
        $skip_count    = 0;

        // Menyiapkan Prepared Statements untuk optimalisasi performa eksekusi dalam perulangan (loop)
        $stmt_check_student = $conn->prepare("SELECT id FROM students WHERE nisn = ? LIMIT 1");
        $stmt_check_class   = $conn->prepare("SELECT id FROM classes WHERE nama_kelas = ? LIMIT 1");
        $stmt_insert_class  = $conn->prepare("INSERT INTO classes (nama_kelas) VALUES (?)");
        $stmt_insert_student = $conn->prepare("
            INSERT INTO students (nisn, nama, class_id, status_warna, level_eskalasi, status_sp, is_probation) 
            VALUES (?, ?, ?, 'hijau', 'teguran', 'tidak_ada', 0)
        ");

        // Memproses baris data demi baris sampai akhir berkas CSV
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            
            // Mengabaikan baris kosong atau baris cacad format
            if (empty($row) || count($row) < max($idx_nisn, $idx_nama, $idx_kelas) + 1) {
                $skip_count++;
                continue;
            }

            // Ekstraksi nilai kolom berdasarkan indeks hasil pemetaan header
            $nisn       = trim($row[$idx_nisn]);
            $nama       = trim($row[$idx_nama]);
            $nama_kelas = trim($row[$idx_kelas]);

            // Skip jika data kritikal di baris tersebut kosong
            if (empty($nisn) || empty($nama) || empty($nama_kelas)) {
                $skip_count++;
                continue;
            }

            try {
                // 1. Validasi Keunikan Data: Cek duplikasi NISN Siswa
                $stmt_check_student->execute([$nisn]);
                if ($stmt_check_student->fetch()) {
                    $skip_count++; // Dilewati karena siswa dengan NISN tersebut sudah ada
                    continue;
                }

                // 2. Manajemen Relasi: Ambil ID Kelas atau Buat Otomatis jika Belum Tersedia
                $stmt_check_class->execute([$nama_kelas]);
                $class_data = $stmt_check_class->fetch(PDO::FETCH_ASSOC);

                if ($class_data) {
                    $class_id = (int)$class_data['id'];
                } else {
                    // Kelas baru terdeteksi, lakukan penambahan data master kelas secara real-time
                    $stmt_insert_class->execute([$nama_kelas]);
                    $class_id = (int)$conn->lastInsertId();
                }

                // 3. Registrasikan data siswa baru ke dalam sistem SinergiCare
                $stmt_insert_student->execute([$nisn, $nama, $class_id]);
                $success_count++;

            } catch (PDOException $e) {
                // Catat baris sebagai gagal jika mengalami kendala query internal
                $skip_count++;
            }
        }
        
        fclose($handle);

        // Penyusunan kesimpulan log akumulasi kedalam sistem flash message
        if ($success_count > 0) {
            setFlash('success', "✨ Impor data selesai! {$success_count} profil siswa berhasil ditambahkan, {$skip_count} baris dilewati.");
        } else {
            setFlash('warning', "🔔 Tidak ada siswa baru yang terdaftar. {$skip_count} baris data dilewati atau tidak valid.");
        }
    } else {
        setFlash('error', '⚠️ Sistem gagal membuka data berkas CSV yang diunggah.');
    }
} else {
    setFlash('error', '⚠️ Permintaan ditolak! Berkas eksternal tidak ditemukan atau metode tidak sah.');
}

// Kembalikan pengguna ke pusat kendali kontrol administrasi
header("Location: /pages/admin.php");
exit();