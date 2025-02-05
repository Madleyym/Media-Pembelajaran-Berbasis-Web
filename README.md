-- Buat database
CREATE DATABASE db_media_pembelajaran;
USE db_media_pembelajaran;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'guru', 'siswa') NOT NULL,
    foto_profile VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Modifikasi tabel users
ALTER TABLE users 
ADD status ENUM('active', 'inactive') DEFAULT 'active',
ADD activation_token VARCHAR(255) DEFAULT NULL,
ADD reset_token VARCHAR(255) DEFAULT NULL,
ADD reset_token_expiry DATETIME DEFAULT NULL;

-- Modifikasi tabel siswa_kelas
ALTER TABLE siswa_kelas 
ADD status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
ADD UNIQUE KEY `unique_siswa_kelas_tahun` (`id_siswa`, `id_kelas`, `tahun_ajaran`);
ALTER TABLE kuis ADD COLUMN id_kelas int(11) NOT NULL;

-- Tambah data default untuk kelas
INSERT INTO kelas (nama_kelas) VALUES 
('Kelas 1'),
('Kelas 2'),
('Kelas 3'),
('Kelas 4'),
('Kelas 5'),
('Kelas 6');

-- Tabel Kelas
CREATE TABLE kelas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kelas VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Mata Pelajaran
CREATE TABLE mata_pelajaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_mapel VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Materi
CREATE TABLE materi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    id_mapel INT NOT NULL,
    id_guru INT NOT NULL,
    tipe_materi ENUM('video', 'audio', 'dokumen') NOT NULL,
    file_materi VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mapel) REFERENCES mata_pelajaran(id),
    FOREIGN KEY (id_guru) REFERENCES users(id)
);

-- Tabel progress_materi
CREATE TABLE progress_materi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    materi_id INT NOT NULL,
    status ENUM('belum', 'selesai') DEFAULT 'belum',
    tanggal_selesai DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES users(id),
    FOREIGN KEY (materi_id) REFERENCES materi(id)
    ALTER TABLE materi ADD id_kelas INT NOT NULL;
);

-- Tabel Kuis
CREATE TABLE kuis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    id_mapel INT NOT NULL,
    id_guru INT NOT NULL,
    waktu_pengerjaan INT NOT NULL, -- dalam menit
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mapel) REFERENCES mata_pelajaran(id),
    FOREIGN KEY (id_guru) REFERENCES users(id)
);

-- Tabel Soal Kuis
CREATE TABLE soal_kuis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_kuis INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    pilihan_a TEXT NOT NULL,
    pilihan_b TEXT NOT NULL,
    pilihan_c TEXT NOT NULL,
    pilihan_d TEXT NOT NULL,
    jawaban_benar ENUM('a', 'b', 'c', 'd') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kuis) REFERENCES kuis(id)
);

-- Tabel Nilai Kuis
CREATE TABLE nilai_kuis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_kuis INT NOT NULL,
    id_siswa INT NOT NULL,
    nilai FLOAT NOT NULL,
    waktu_mulai DATETIME NOT NULL,
    waktu_selesai DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kuis) REFERENCES kuis(id),
    FOREIGN KEY (id_siswa) REFERENCES users(id)
);

-- Tabel Progress Belajar
CREATE TABLE progress_belajar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_siswa INT NOT NULL,
    id_materi INT NOT NULL,
    status ENUM('belum', 'selesai') DEFAULT 'belum',
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES users(id),
    FOREIGN KEY (id_materi) REFERENCES materi(id)
);

-- Tabel Siswa Kelas
CREATE TABLE siswa_kelas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_siswa INT NOT NULL,
    id_kelas INT NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES users(id),
    FOREIGN KEY (id_kelas) REFERENCES kelas(id)
);

CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    attempt_time DATETIME NOT NULL,
    success TINYINT(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



---------


-- Insert data default untuk admin
INSERT INTO users (username, password, nama_lengkap, email, role) 
VALUES ('admin', '$2y$10$YOUR_HASHED_PASSWORD', 'Administrator', 'admin@example.com', 'admin');

Media Pembelajaran Berbasis Web untuk Siswa SD dengan Metode Visual dan Audio

Struktur Website Media Pembelajaran:
root/
│
├── config/
│   ├── constants.php          # Konfigurasi database dan constant
│   └── koneksi.php         # Koneksi database
│
├── assets/
│   ├── css/                # File-file CSS
│   ├── js/                 # File-file JavaScript
│   ├── images/             # Gambar-gambar
│   ├── audio/              # File audio pembelajaran
│   └── video/              # File video pembelajaran
│
├── models/
│   ├── User.php            # Model untuk manajemen user
│   ├── Materi.php          # Model untuk manajemen materi
│   ├── Kuis.php            # Model untuk manajemen kuis
│   └── Nilai.php           # Model untuk manajemen nilai
│
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── manage_users.php
│   │   ├── manage_materi.php
│   │   └── manage_kuis.php
│   │
│   ├── siswa/
│   │   ├── dashboard.php
│   │   ├── materi.php
│   │   ├── kuis.php
│   │   └── nilai.php
│   │
│   ├── guru/
│   │   ├── dashboard.php
│   │   ├── upload_materi.php
│   │   ├── buat_kuis.php
│   │   └── nilai_siswa.php
│   │
│   └── templates/
│       ├── footer.php
│       └── navigation.php
│
├── controllers/
│   ├── AuthController.php   # Controller untuk autentikasi
│   ├── AdminController.php  # Controller untuk admin
│   ├── SiswaController.php  # Controller untuk siswa
│   ├── GuruController.php   # Controller untuk guru
│   ├── MateriController.php # Controller untuk materi
│   └── KuisController.php   # Controller untuk kuis
│
├── includes/
│   ├── functions.php        # Helper functions
│   └── validation.php       # Form validation
│
├── uploads/                 # Folder untuk upload file
│   ├── materi/
│   ├── tugas/
│   └── profile/
│
├── vendor/                  # Dependencies (jika menggunakan composer)
│
├── .htaccess               # File konfigurasi Apache
├── index.php               # File utama (front controller)
└── README.md               # Dokumentasi project