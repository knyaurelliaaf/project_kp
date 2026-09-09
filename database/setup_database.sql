CREATE TABLE IF NOT EXISTS `rig` (
  `id_rig` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_rig` VARCHAR(50) NOT NULL UNIQUE,
  `nama_rig` VARCHAR(100) NOT NULL,
  `status` ENUM('aktif', 'nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin_rig', 'payroll', 'payroll_wa', 'admin_gaji') DEFAULT 'admin_rig',
  `status_aktif` ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_rig` (
  `id_user` INT NOT NULL,
  `id_rig` INT NOT NULL,
  PRIMARY KEY (`id_user`, `id_rig`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `crew` (
  `id_crew` INT AUTO_INCREMENT PRIMARY KEY,
  `id_rig` INT NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `posisi` VARCHAR(100) DEFAULT NULL,
  `crew` VARCHAR(50) DEFAULT NULL,
  `status_aktif` ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
  `tanggal_nonaktif` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `badge` (
  `id_badge` INT AUTO_INCREMENT PRIMARY KEY,
  `id_crew` INT NOT NULL,
  `nomor_badge` VARCHAR(50) DEFAULT NULL,
  `tanggal_expired` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mcu` (
  `id_mcu` INT AUTO_INCREMENT PRIMARY KEY,
  `id_crew` INT NOT NULL,
  `tanggal_mcu` DATE DEFAULT NULL,
  `derajat_kesehatan` VARCHAR(50) DEFAULT NULL,
  `tanggal_mcu_berikutnya` DATE DEFAULT NULL,
  `expired` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pkwt` (
  `id_pkwt` INT AUTO_INCREMENT PRIMARY KEY,
  `id_crew` INT NOT NULL,
  `tanggal_berakhir` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sertifikat` (
  `id_sertifikat` INT AUTO_INCREMENT PRIMARY KEY,
  `id_crew` INT NOT NULL,
  `jenis` VARCHAR(100) DEFAULT NULL,
  `tanggal_expired` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `jenis_surat` (
  `id_jenis` INT AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(20) NOT NULL UNIQUE,
  `nama_surat` VARCHAR(150) NOT NULL,
  `format_nomor` VARCHAR(255) NOT NULL,
  `keterangan` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `surat` (
  `id_surat` INT AUTO_INCREMENT PRIMARY KEY,
  `id_rig` INT NOT NULL,
  `id_jenis` INT NOT NULL,
  `id_user` INT NOT NULL,
  `nomor_surat` VARCHAR(100) NOT NULL UNIQUE,
  `tahun` INT NOT NULL,
  `bulan` INT NOT NULL,
  `no_urut` INT NOT NULL,
  `tanggal_moc` DATE DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `link_file` TEXT DEFAULT NULL,
  `nama_file` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `slip_gaji_master` (
  `id_slip` INT AUTO_INCREMENT PRIMARY KEY,
  `id_rig` INT DEFAULT NULL,
  `id_crew` INT DEFAULT NULL,
  `periode` VARCHAR(50) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `no_urut` VARCHAR(10) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `jabatan` VARCHAR(100) NOT NULL,
  `badge_excel` VARCHAR(50) NOT NULL DEFAULT '',
  `gaji_bersih` DECIMAL(15,2) DEFAULT 0,
  `data_json` JSON NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `slip_gaji_pending` (
  `id_pending` INT AUTO_INCREMENT PRIMARY KEY,
  `periode` VARCHAR(50) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `no_urut` VARCHAR(10) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `jabatan` VARCHAR(100) NOT NULL,
  `badge_excel` VARCHAR(50) NOT NULL DEFAULT '',
  `data_json` JSON NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_pending_periode_no_urut` (`periode`, `no_urut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `master_posisi` (
  `id_posisi` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_posisi` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `log_aktivitas` (
  `id_log` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT DEFAULT NULL,
  `aktivitas` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data Rigs
INSERT IGNORE INTO `rig` (`id_rig`, `kode_rig`, `nama_rig`, `status`) VALUES
(1, 'GW-336', 'RIG GW-336', 'aktif'),
(2, 'GW-337', 'RIG GW-337', 'aktif'),
(3, 'GW-338', 'RIG GW-338', 'aktif'),
(4, 'GW-339', 'RIG GW-339', 'aktif');

-- Seed Data User (admin@gmail.com & admin1@gmail.com / admin123)
INSERT IGNORE INTO `user` (`id_user`, `nama`, `email`, `password`, `role`, `status_aktif`) VALUES
(1, 'Super Admin', 'admin@gmail.com', '$2y$10$ZIggv3uvrj.IRYsdGvBOh.Df6Fe9X0igovz/q.axhftJeWTBZAamS', 'super_admin', 'aktif'),
(2, 'Admin Payroll', 'admin1@gmail.com', '$2y$10$ZIggv3uvrj.IRYsdGvBOh.Df6Fe9X0igovz/q.axhftJeWTBZAamS', 'payroll', 'aktif');

-- Seed Data Jenis Surat
INSERT IGNORE INTO `jenis_surat` (`id_jenis`, `kode`, `nama_surat`, `format_nomor`) VALUES
(1, 'SPK', 'Surat Perintah Kerja', 'SPK/{rig}/ADK/SUB GDAP/{tahun}/{bulan}/{no}'),
(2, 'MCU', 'Pengajuan MCU Crew', 'MCU/{kode}/{rig}/{tahun}/{bulan}/{no}'),
(3, 'PHK', 'Surat Pemutusan Hubungan Kerja', 'PHK/{rig}/ADK/SUB GDAP/{tahun}/{bulan}/{no}'),
(4, 'SR', 'Surat Rekomendasi', 'SR/{rig}/ADK/SUB GDAP/{tahun}/{bulan}/{no}'),
(5, 'MM', 'Internal Memorandum', 'MM/{rig}/ADK/SUB GDAP/{tahun}/{bulan}/{no}');

-- Tabel Penerima Slip Gaji WA
CREATE TABLE IF NOT EXISTS `penerima_slip` (
    `id_penerima` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `nik` VARCHAR(30) DEFAULT NULL,
    `nomor_wa` VARCHAR(20) NOT NULL,
    `id_rig` INT DEFAULT NULL,
    `kode_rig` VARCHAR(50) DEFAULT NULL,
    `crew` VARCHAR(50) DEFAULT NULL,
    `posisi` VARCHAR(100) DEFAULT NULL,
    `status_aktif` ENUM('aktif','nonaktif') DEFAULT 'aktif',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Log Pengiriman Slip Gaji WA
CREATE TABLE IF NOT EXISTS `kirim_log` (
    `id_log` INT AUTO_INCREMENT PRIMARY KEY,
    `id_penerima` INT NOT NULL,
    `periode` VARCHAR(20) NOT NULL,
    `nama_file` VARCHAR(100),
    `status` ENUM('pending','terkirim','gagal') DEFAULT 'pending',
    `pesan_error` TEXT NULL,
    `dikirim_pada` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_penerima`) REFERENCES `penerima_slip`(`id_penerima`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

