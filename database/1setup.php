<?php
// database/setup.php — DATABASE AUTO-INITIALIZER ENGINE FOR SINERGICARE
// PERBAIKAN H3: cek apakah database sudah ada sebelum dijalankan ulang
header("Content-Type: text/plain");

$host   = "localhost";
$user   = "root";
$pass   = "root"; // Sesuaikan dengan password MySQL Anda
$dbname = "sinergicare_smk";

// PERBAIKAN H3: blokir re-eksekusi jika credentials sudah ada
$credentials_file = __DIR__ . '/../config/db_credentials.json';
if (file_exists($credentials_file)) {
    echo "====================================================\n";
    echo " ⛔ SETUP DIBLOKIR\n";
    echo "====================================================\n\n";
    echo "File db_credentials.json sudah ada — database kemungkinan sudah tersetup.\n";
    echo "Hapus file db_credentials.json terlebih dahulu jika ingin setup ulang.\n\n";
    echo "Atau akses: /database/reset_complete.php (butuh login Super Admin)\n";
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "====================================================\n";
    echo " ⚡ SINERGICARE v3.0 DATABASE SETUP WIZARD\n";
    echo "====================================================\n\n";

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✔ Database `$dbname` berhasil diverifikasi/dibuat.\n";

    $pdo->exec("USE `$dbname`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = ['log_surat', 'sp_records', 'journals', 'incidents', 'consequences', 'user_roles', 'roles', 'students', 'classes', 'staf_sekolah', 'violation_categories'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }
    echo "✔ Pembersihan tabel usang selesai.\n\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ============================================================
    // STRUKTUR TABEL 
    // ============================================================
    $pdo->exec("CREATE TABLE `classes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama_kelas` VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `violation_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama_kejadian` VARCHAR(255) NOT NULL,
        `bobot_risiko` VARCHAR(20) NOT NULL
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `staf_sekolah` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama` VARCHAR(150) NOT NULL,
        `email` VARCHAR(100) NULL,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `roles` VARCHAR(50) NOT NULL DEFAULT 'guru',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `roles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama_role` VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `user_roles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `role_id` INT NOT NULL,
        FOREIGN KEY (`user_id`) REFERENCES `staf_sekolah`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `students` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nisn` VARCHAR(20) NOT NULL UNIQUE,
        `nama` VARCHAR(150) NOT NULL,
        `class_id` INT NULL,
        `status_warna` VARCHAR(20) NOT NULL DEFAULT 'hijau',
        `level_eskalasi` VARCHAR(50) NOT NULL DEFAULT 'teguran',
        `status_sp` VARCHAR(20) NOT NULL DEFAULT 'tidak_ada',
        `is_probation` INT NOT NULL DEFAULT 0,
        `probation_end` DATE NULL,
        FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `incidents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `category_id` INT NOT NULL,
        `user_id` INT NULL,
        `catatan` TEXT NOT NULL,
        `lokasi_kejadian` VARCHAR(100) NULL,
        `tanggal_kejadian` DATE NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`)             ON DELETE CASCADE,
        FOREIGN KEY (`category_id`) REFERENCES `violation_categories`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`)     REFERENCES `staf_sekolah`(`id`)         ON DELETE SET NULL
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `journals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `category_id` INT NOT NULL,
        `catatan` TEXT NOT NULL,
        `lokasi_kejadian` VARCHAR(100) NULL,
        `tanggal_kejadian` DATE NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`)             ON DELETE CASCADE,
        FOREIGN KEY (`user_id`)     REFERENCES `staf_sekolah`(`id`)         ON DELETE CASCADE,
        FOREIGN KEY (`category_id`) REFERENCES `violation_categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `consequences` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `deskripsi_tugas` TEXT NOT NULL,
        `penanggung_jawab` INT NOT NULL,
        `status_tugas` VARCHAR(20) NOT NULL DEFAULT 'proses',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `completed_at` TIMESTAMP NULL,
        FOREIGN KEY (`student_id`)       REFERENCES `students`(`id`)      ON DELETE CASCADE,
        FOREIGN KEY (`penanggung_jawab`) REFERENCES `staf_sekolah`(`id`)  ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `sp_records` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `tingkat_sp` VARCHAR(20) NOT NULL,
        `alasan_sp` TEXT NOT NULL,
        `diterbitkan_oleh` INT NOT NULL,
        `is_approved` INT NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`)       REFERENCES `students`(`id`)     ON DELETE CASCADE,
        FOREIGN KEY (`diterbitkan_oleh`) REFERENCES `staf_sekolah`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE `log_surat` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `tipe_surat` VARCHAR(50) NOT NULL,
        `tanggal_surat` DATE NULL,
        `jam_surat` TIME NULL,
        `dibuat_oleh` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`)     ON DELETE CASCADE,
        FOREIGN KEY (`dibuat_oleh`) REFERENCES `staf_sekolah`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    echo "✔ Seluruh struktur tabel berhasil di-compile.\n\n";

    // ============================================================
    // DATA SEEDING (HANYA ADMIN)
    // ============================================================
    echo "----------------------------------------------------\n";
    echo " ⚙️  MEMULAI PROSES SEEDING...\n";
    echo "----------------------------------------------------\n";

    // 1. Memasukkan Data Role Dasar (Dibutuhkan untuk hak akses)
    $roles = [['1', 'admin'], ['2', 'guru'], ['3', 'bk'], ['4', 'yayasan'], ['5', 'super_admin']];
    $stmt  = $pdo->prepare("INSERT INTO `roles` (id, nama_role) VALUES (?, ?)");
    foreach ($roles as $r) $stmt->execute($r);

    // 2. Memasukkan 1 Akun Default (Super Admin)
    $staf = [
        ['1', 'Administrator Utama', 'super@smk.sch.id', 'admin', 'admin123', 'super_admin']
    ];
    $stmt = $pdo->prepare("INSERT INTO `staf_sekolah` (id, nama, email, username, password, roles) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($staf as $s) {
        $s[4] = password_hash($s[4], PASSWORD_BCRYPT); // hash password sebelum insert
        $stmt->execute($s);
        echo "✔ Akun '{$s[3]}' dibuat (password ter-hash).\n";
    }

    // 3. Menghubungkan Akun Admin dengan Role 'super_admin'
    $u_roles = [['1', '1', '5']];
    $stmt    = $pdo->prepare("INSERT INTO `user_roles` (id, user_id, role_id) VALUES (?, ?, ?)");
    foreach ($u_roles as $ur) $stmt->execute($ur);

    // Menyimpan kredensial untuk memblokir setup berulang
    $credentials = json_encode([
        'host'     => $host,
        'username' => $user,
        'password' => $pass,
        'db_name'  => $dbname,
    ], JSON_PRETTY_PRINT);
    file_put_contents(__DIR__ . '/../config/db_credentials.json', $credentials);

    echo "\n✔ Proses Seeding 100% Selesai.\n\n";
    echo "====================================================\n";
    echo " 🎉 SYSTEM IS READY!\n";
    echo " ⚠️  PENTING: Hapus atau rename file setup.php ini!\n";
    echo "====================================================\n";
    echo "\nKredensial Login Default:\n";
    echo "  Username : admin\n";
    echo "  Password : admin123\n";
    echo "\nPassword telah diamankan dengan enkripsi bcrypt.\n";

} catch (PDOException $e) {
    echo "\n❌ [FATAL ERROR]: " . $e->getMessage() . "\n";
}