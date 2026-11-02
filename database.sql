CREATE DATABASE IF NOT EXISTS si_fotocopy_ija CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE si_fotocopy_ija;

CREATE TABLE IF NOT EXISTS users (
 id_user INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(100) NOT NULL,
 username VARCHAR(60) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
 role ENUM('pemilik','pegawai') NOT NULL DEFAULT 'pegawai', dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS pelanggan (
 id_pelanggan INT AUTO_INCREMENT PRIMARY KEY, nama_pelanggan VARCHAR(100) NOT NULL,
 no_hp VARCHAR(25) DEFAULT NULL, dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS layanan (
 id_layanan INT AUTO_INCREMENT PRIMARY KEY, nama_layanan VARCHAR(50) NOT NULL,
 tarif INT NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS pesanan (
 id_pesanan INT AUTO_INCREMENT PRIMARY KEY, tanggal_pesanan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 jumlah_dokumen INT NOT NULL, total_biaya INT NOT NULL,
 id_pelanggan INT DEFAULT NULL, status_pesanan ENUM('menunggu','diproses','selesai') NOT NULL DEFAULT 'menunggu',
 catatan VARCHAR(255) DEFAULT NULL,
 CONSTRAINT fk_pesanan_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS dokumen (
 id_dokumen INT AUTO_INCREMENT PRIMARY KEY, id_pesanan INT NOT NULL,
 jenis_dokumen VARCHAR(50) NOT NULL, kondisi_dokumen VARCHAR(50) NOT NULL,
 CONSTRAINT fk_dokumen_pesanan FOREIGN KEY(id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS detail_pesanan (
 id_pesanan INT NOT NULL, id_layanan INT NOT NULL, jumlah INT NOT NULL DEFAULT 1,
 harga_satuan INT NOT NULL DEFAULT 0, subtotal INT NOT NULL DEFAULT 0,
 PRIMARY KEY(id_pesanan,id_layanan),
 CONSTRAINT fk_detail_pesanan FOREIGN KEY(id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
 CONSTRAINT fk_detail_layanan FOREIGN KEY(id_layanan) REFERENCES layanan(id_layanan) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS transaksi (
 id_transaksi INT AUTO_INCREMENT PRIMARY KEY, id_pesanan INT NOT NULL UNIQUE,
 jumlah_bayar INT NOT NULL, waktu_pembayaran DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_transaksi_pesanan FOREIGN KEY(id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE
) ENGINE=InnoDB;
