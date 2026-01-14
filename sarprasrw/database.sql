-- Database: sarpras_rw
-- Sistem Informasi Peminjaman Sarana dan Prasarana RW

CREATE DATABASE IF NOT EXISTS sarpras_rw;
USE sarpras_rw;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(20),
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'warga') NOT NULL DEFAULT 'warga',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel sarpras
CREATE TABLE sarpras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    kondisi ENUM('baik', 'rusak', 'perlu_perbaikan') NOT NULL DEFAULT 'baik',
    status ENUM('tersedia', 'dipinjam', 'rusak') NOT NULL DEFAULT 'tersedia',
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel peminjaman
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM('menunggu', 'disetujui', 'ditolak', 'selesai') NOT NULL DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel detail_peminjaman
CREATE TABLE detail_peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    sarpras_id INT NOT NULL,
    jumlah_pinjam INT NOT NULL,
    kondisi_kembali ENUM('baik', 'rusak', 'perlu_perbaikan'),
    catatan TEXT,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (sarpras_id) REFERENCES sarpras(id) ON DELETE CASCADE
);

-- Insert sample data

-- Admin user
INSERT INTO users (nama, alamat, no_hp, username, password, role) VALUES
('Admin RW', 'Jl. RW No. 1', '081234567890', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

-- Sample warga
INSERT INTO users (nama, alamat, no_hp, username, password, role) VALUES
('Ahmad Surya', 'Jl. Melati No. 10', '081234567891', 'ahmad', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga'),
('Siti Aminah', 'Jl. Anggrek No. 15', '081234567892', 'siti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga'),
('Budi Santoso', 'Jl. Mawar No. 20', '081234567893', 'budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga');

-- Sample sarpras
INSERT INTO sarpras (nama, kategori, jumlah, kondisi, status) VALUES
('Meja Lipat', 'Furniture', 10, 'baik', 'tersedia'),
('Kursi Plastik', 'Furniture', 20, 'baik', 'tersedia'),
('Proyektor', 'Elektronik', 2, 'baik', 'tersedia'),
('Speaker', 'Elektronik', 3, 'baik', 'tersedia'),
('Tenda', 'Outdoor', 5, 'baik', 'tersedia'),
('Panggung', 'Outdoor', 1, 'baik', 'tersedia'),
('Mic Wireless', 'Elektronik', 2, 'rusak', 'rusak'),
('Kabel Extension', 'Elektronik', 5, 'baik', 'tersedia');

-- Sample peminjaman
INSERT INTO peminjaman (user_id, tanggal_pinjam, tanggal_kembali, status) VALUES
(2, '2024-01-15', '2024-01-16', 'selesai'),
(3, '2024-01-20', '2024-01-22', 'disetujui'),
(2, '2024-01-25', '2024-01-26', 'menunggu');

-- Sample detail_peminjaman
INSERT INTO detail_peminjaman (peminjaman_id, sarpras_id, jumlah_pinjam, kondisi_kembali, catatan) VALUES
(1, 1, 5, 'baik', 'Dikembalikan dalam kondisi baik'),
(1, 2, 10, 'baik', 'Dikembalikan dalam kondisi baik'),
(2, 3, 1, NULL, NULL),
(2, 4, 2, NULL, NULL),
(3, 5, 2, NULL, NULL);
