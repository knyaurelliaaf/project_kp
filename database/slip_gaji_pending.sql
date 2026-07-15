CREATE TABLE IF NOT EXISTS `slip_gaji_pending` (
    `id_pending` INT NOT NULL AUTO_INCREMENT,
    `periode` VARCHAR(50) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `no_urut` VARCHAR(10) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `jabatan` VARCHAR(100) NOT NULL,
    `badge_excel` VARCHAR(50) NOT NULL DEFAULT '',
    `data_json` JSON NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pending`),
    UNIQUE KEY `unique_pending_periode_no_urut` (`periode`, `no_urut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
