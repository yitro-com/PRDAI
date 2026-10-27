-- PRDAI — Skema basis data.
-- Engine InnoDB, charset utf8mb4. Impor berkas ini sekali saat pemasangan.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Tabel: pengguna
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `kata_sandi` VARCHAR(255) NOT NULL,
  `token_ingat` VARCHAR(255) NULL DEFAULT NULL,
  `ip_terkunci` VARCHAR(45) NULL DEFAULT NULL,
  `ingat_kadaluarsa` DATETIME NULL DEFAULT NULL,
  `provider` VARCHAR(50) NOT NULL DEFAULT 'lokal',
  `provider_id` VARCHAR(190) NULL DEFAULT NULL,
  `avatar` VARCHAR(255) NULL DEFAULT NULL,
  `zona_waktu` VARCHAR(50) NULL DEFAULT NULL,
  `peran` ENUM('pengguna','admin') NOT NULL DEFAULT 'pengguna',
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `terakhir_masuk` DATETIME NULL DEFAULT NULL,
  `dibuat_pada` DATETIME NOT NULL,
  `diperbarui_pada` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: prd
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prd` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` CHAR(16) NOT NULL,
  `pengguna_id` BIGINT UNSIGNED NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `jenis_produk` VARCHAR(40) NOT NULL,
  `masukan_wizard` LONGTEXT NULL DEFAULT NULL,
  `isi_markdown` LONGTEXT NULL DEFAULT NULL,
  `status` ENUM('draf','menyusun','selesai','gagal') NOT NULL DEFAULT 'draf',
  `versi_saat_ini` INT UNSIGNED NOT NULL DEFAULT 0,
  `dibuat_pada` DATETIME NOT NULL,
  `diperbarui_pada` DATETIME NOT NULL,
  `dihapus_pada` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_kode` (`kode`),
  KEY `idx_pengguna_aktif` (`pengguna_id`, `dihapus_pada`, `diperbarui_pada`),
  CONSTRAINT `fk_prd_pengguna` FOREIGN KEY (`pengguna_id`)
    REFERENCES `pengguna` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: prd_versi
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prd_versi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prd_id` BIGINT UNSIGNED NOT NULL,
  `nomor_versi` INT UNSIGNED NOT NULL,
  `isi_markdown` LONGTEXT NOT NULL,
  `sumber` ENUM('ai','manual','pemulihan') NOT NULL DEFAULT 'manual',
  `catatan` VARCHAR(255) NULL DEFAULT NULL,
  `dibuat_pada` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_prd_versi` (`prd_id`, `nomor_versi`),
  KEY `idx_prd` (`prd_id`),
  CONSTRAINT `fk_versi_prd` FOREIGN KEY (`prd_id`)
    REFERENCES `prd` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: percobaan_masuk
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `percobaan_masuk` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `alamat_ip` VARCHAR(45) NOT NULL,
  `berhasil` TINYINT(1) NOT NULL DEFAULT 0,
  `dibuat_pada` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email_ip_waktu` (`email`, `alamat_ip`, `dibuat_pada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: batas_laju
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `batas_laju` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kunci` VARCHAR(120) NOT NULL,
  `jumlah` INT NOT NULL DEFAULT 0,
  `jendela_mulai` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_kunci` (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: penggunaan_ai
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `penggunaan_ai` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pengguna_id` BIGINT UNSIGNED NOT NULL,
  `prd_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `model` VARCHAR(80) NOT NULL,
  `token_masuk` INT UNSIGNED NOT NULL DEFAULT 0,
  `token_keluar` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('sukses','gagal') NOT NULL DEFAULT 'sukses',
  `dibuat_pada` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengguna_waktu` (`pengguna_id`, `dibuat_pada`),
  CONSTRAINT `fk_penggunaan_pengguna` FOREIGN KEY (`pengguna_id`)
    REFERENCES `pengguna` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: log_aktivitas
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_aktivitas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pengguna_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `aksi` VARCHAR(60) NOT NULL,
  `keterangan` VARCHAR(255) NULL DEFAULT NULL,
  `alamat_ip` VARCHAR(45) NULL DEFAULT NULL,
  `agen_pengguna` VARCHAR(255) NULL DEFAULT NULL,
  `dibuat_pada` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengguna` (`pengguna_id`),
  KEY `idx_aksi_waktu` (`aksi`, `dibuat_pada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel: ci_sessions (sesi di basis data, sesuai CI3)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `timestamp` INT UNSIGNED NOT NULL DEFAULT 0,
  `data` BLOB NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;