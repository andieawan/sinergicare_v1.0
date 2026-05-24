-- ============================================
-- Database INDEX Creation Script
-- SinergiCare SMK v2.0
-- ============================================

-- 1. INDEX untuk TABLE: students
ALTER TABLE `students` ADD INDEX `idx_status_warna` (`status_warna`);
ALTER TABLE `students` ADD INDEX `idx_class_id` (`class_id`);
ALTER TABLE `students` ADD INDEX `idx_status_class` (`status_warna`, `class_id`);
ALTER TABLE `students` ADD INDEX `idx_nama` (`nama`);
ALTER TABLE `students` ADD INDEX `idx_nisn` (`nisn`);

-- 2. INDEX untuk TABLE: incidents
ALTER TABLE `incidents` ADD INDEX `idx_student_id` (`student_id`);
ALTER TABLE `incidents` ADD INDEX `idx_category_id` (`category_id`);
ALTER TABLE `incidents` ADD INDEX `idx_created_at` (`created_at`);

-- 3. INDEX untuk TABLE: journals
ALTER TABLE `journals` ADD INDEX `idx_student_id` (`student_id`);
ALTER TABLE `journals` ADD INDEX `idx_user_id` (`user_id`);
ALTER TABLE `journals` ADD INDEX `idx_category_id` (`category_id`);
ALTER TABLE `journals` ADD INDEX `idx_created_at` (`created_at`);
ALTER TABLE `journals` ADD INDEX `idx_student_created` (`student_id`, `created_at`);

-- 4. INDEX untuk TABLE: consequences
-- CATATAN: kolom bk_id TIDAK ADA di tabel ini, dihapus dari daftar
ALTER TABLE `consequences` ADD INDEX `idx_student_id` (`student_id`);
ALTER TABLE `consequences` ADD INDEX `idx_status` (`status_tugas`);
ALTER TABLE `consequences` ADD INDEX `idx_penanggung_jawab` (`penanggung_jawab`);

-- 5. INDEX untuk TABLE: classes
ALTER TABLE `classes` ADD INDEX `idx_nama_kelas` (`nama_kelas`);

-- 6. INDEX untuk TABLE: staf_sekolah
ALTER TABLE `staf_sekolah` ADD INDEX `idx_username` (`username`);
ALTER TABLE `staf_sekolah` ADD INDEX `idx_email` (`email`);
ALTER TABLE `staf_sekolah` ADD INDEX `idx_roles` (`roles`);

-- 7. INDEX untuk TABLE: violation_categories
ALTER TABLE `violation_categories` ADD INDEX `idx_nama_kejadian` (`nama_kejadian`);
ALTER TABLE `violation_categories` ADD INDEX `idx_bobot_risiko` (`bobot_risiko`);
