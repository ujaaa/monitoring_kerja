-- =============================================
-- Monitoring Kerja — Database Schema
-- Jalankan: mysql -u root -p < database/database.sql
-- atau import via phpMyAdmin → Database → Import
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

--
-- Database: `monitoring_kerja`
--
CREATE DATABASE IF NOT EXISTS `monitoring_kerja`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `monitoring_kerja`;

--
-- Tabel `users` — data pengguna sistem
--
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(150) NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'supervisor', 'admin') NOT NULL DEFAULT 'user',
    `foto` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tabel `tasks` — data pekerjaan / tugas
--
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `assigned_to` INT NOT NULL,
    `assigned_by` INT NULL,
    `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `deadline` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tasks_assigned_to`
        FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tasks_assigned_by`
        FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index untuk pencarian & filter performa
--
CREATE INDEX `idx_tasks_assigned_to` ON `tasks`(`assigned_to`);
CREATE INDEX `idx_tasks_status` ON `tasks`(`status`);
CREATE INDEX `idx_tasks_priority` ON `tasks`(`priority`);
CREATE INDEX `idx_tasks_deadline` ON `tasks`(`deadline`);
CREATE INDEX `idx_tasks_assigned_by` ON `tasks`(`assigned_by`);
CREATE INDEX `idx_users_username` ON `users`(`username`);
CREATE INDEX `idx_users_role` ON `users`(`role`);

--
-- Data dummy — seed user
-- Password: bcrypt hash via password_hash().
-- Gunakan config/seed.php untuk seed otomatis, atau insert manual di bawah.
--

INSERT INTO `users` (`nama`, `username`, `email`, `password`, `role`) VALUES
    ('Admin', 'admin', 'admin@kerja.id', '$2y$12$POVQTNf/6hrFXWNiydLAk.1kQuuqZUeD5GOaCHv2mKspi4RorL8jW', 'admin'),
    ('Sari', 'sari', 'sari@kerja.id', '$2y$12$ZcOqKoj7/bGXTP.zDHVSCeTAzZVxjYlYkPPAAZL63UHxV1XoYAQ6S', 'supervisor'),
    ('Budi', 'budi', 'budi@kerja.id', '$2y$12$z9krTi/1gAYVT1Uhaqvvv.JNbc/BHgBKvye0RRZiPfPQ/9qVX8tnu', 'supervisor'),
    ('Dewi', 'dewi', 'dewi@kerja.id', '$2y$12$IbGtPtMmXpXZKIZ71LApPeL/iNb9wuaJzrmddHzlL/88XRbFsjU8G', 'user'),
    ('Raka', 'raka', 'raka@kerja.id', '$2y$12$gxkEVXQSAXsQhum3scNTdu3SPWc.O9w058JK5904IeEw1xFPZyoTu', 'user'),
    ('Maya', 'maya', 'maya@kerja.id', '$2y$12$gXohg2ppPdY1lKZQkzjJV.7GzG2IXBwBelgxtbwo9SPejxO95ofBK', 'user')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

--
-- Data dummy — seed tugas
-- Deadline sudah disetel relatif terhadap tahun 2026.
-- Jalankan config/seed.php untuk regenerasi data tugas secara otomatis.
--

INSERT INTO `tasks` (`title`, `description`, `assigned_to`, `assigned_by`, `priority`, `status`, `deadline`) VALUES
    ('Laporan penjualan Q3', 'Laporan penjualan triwulan ketiga.', 2, 1, 'high', 'in_progress', '2026-09-05'),
    ('Update website landing', 'Perbarui konten halaman depan.', 4, 1, 'medium', 'in_progress', '2026-09-12'),
    ('Persiapan rapat mingguan', 'Susun agenda dan materi rapat.', 3, 1, 'high', 'in_progress', '2026-09-07'),
    ('Testing fitur login', 'Uji login, register, reset password.', 5, 1, 'medium', 'in_progress', '2026-09-14'),
    ('Desain mockup mobile', 'Mockup halaman task untuk mobile.', 6, 1, 'low', 'in_progress', '2026-09-17'),
    ('Audit keamanan sistem', 'Cek SQL injection, XSS, CSRF.', 2, 1, 'high', 'pending', '2026-09-21'),
    ('Optimasi query database', 'Percepat task_list dan task_count.', 3, 1, 'medium', 'pending', '2026-09-19'),
    ('Dokumentasi API', 'Tulis dokumentasi endpoint.', 4, 1, 'low', 'pending', '2026-09-30'),
    ('Backup database rutin', 'Setup cron backup harian.', 5, 1, 'high', 'pending', '2026-09-10'),
    ('Review pull request', 'Review 3 PR yang pending.', 6, 1, 'medium', 'pending', '2026-09-13'),
    ('Setup hosting production', 'Deploy ke VPS.', 2, 1, 'high', 'completed', '2026-08-27'),
    ('Install SSL certificate', "Let's Encrypt untuk domain.", 3, 1, 'high', 'completed', '2026-08-25'),
    ('Konfigurasi firewall', 'Buka hanya 80 dan 443.', 4, 1, 'medium', 'completed', '2026-08-22'),
    ('Migrasi database lama', 'Pindah dari MySQL ke MariaDB.', 5, 1, 'high', 'completed', '2026-08-18'),
    ('Training tim', 'Pelatihan penggunaan sistem.', 6, 1, 'low', 'completed', '2026-08-13'),
    ('Fix bug upload foto', 'Foto tidak muncul di profil.', 2, 1, 'high', 'in_progress', '2026-09-09'),
    ('Tambah pagination', 'List task harus paginated.', 3, 1, 'medium', 'pending', '2026-09-15'),
    ('Export laporan CSV', 'User bisa download laporan.', 4, 1, 'low', 'pending', '2026-09-22'),
    ('Notifikasi email', 'Kirim email saat task ditugaskan.', 5, 1, 'medium', 'pending', '2026-09-27'),
    ('Integrasi WhatsApp', 'Notifikasi via WhatsApp API.', 6, 1, 'high', 'pending', '2026-10-02')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- Selesai. Database monitoring_kerja siap digunakan.
-- Jalankan config/seed.php untuk seed otomatis.
-- =============================================
