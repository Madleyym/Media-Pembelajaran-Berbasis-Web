# Media Pembelajaran Berbasis Web untuk Siswa SD

Aplikasi pembelajaran berbasis web yang dirancang khusus untuk siswa SD dengan metode visual dan audio. Sistem ini memungkinkan interaksi antara guru dan siswa dalam proses pembelajaran digital.

## 🚀 Fitur Utama

- Multi-user system (Admin, Guru, dan Siswa)
- Manajemen materi pembelajaran (Video, Audio, dan Dokumen)
- Sistem kuis interaktif
- Progress tracking untuk siswa
- Manajemen kelas dan mata pelajaran
- Penilaian otomatis

## 💻 Teknologi yang Digunakan

- PHP
- MySQL
- HTML/CSS
- JavaScript
- Bootstrap (untuk UI/UX)

## 📁 Struktur Proyek

```
root/
├── config/
│   ├── constants.php         # Konfigurasi database dan constant
│   └── koneksi.php           # Koneksi database
├── assets/
│   ├── css/                  # File-file CSS
│   ├── js/                   # File-file JavaScript
│   ├── images/               # Gambar-gambar
│   ├── audio/                # File audio pembelajaran
│   └── video/                # File video pembelajaran
├── models/
│   ├── User.php              # Model untuk manajemen user
│   ├── Materi.php            # Model untuk manajemen materi
│   ├── Kuis.php              # Model untuk manajemen kuis
│   └── Nilai.php             # Model untuk manajemen nilai
├── views/
│   ├── auth/
│   ├── admin/
│   ├── siswa/
│   ├── guru/
│   └── templates/
├── controllers/
│   ├── AuthController.php    # Controller untuk autentikasi
│   ├── AdminController.php   # Controller untuk admin
│   ├── SiswaController.php   # Controller untuk siswa
│   ├── GuruController.php    # Controller untuk guru
│   ├── MateriController.php  # Controller untuk materi
│   └── KuisController.php    # Controller untuk kuis
├── includes/
│   ├── functions.php         # Helper functions
│   └── validation.php        # Form validation
├── uploads/                  # Folder untuk upload file
└── vendor/                   # Dependencies
```

## 🛠️ Instalasi

1. Clone repository ini
```bash
git clone [url-repository]
```

2. Import database
```bash
mysql -u username -p database_name < db_media_pembelajaran.sql
```

3. Konfigurasi database
- Buka file `config/constants.php`
- Sesuaikan konfigurasi database:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'db_media_pembelajaran');
```

4. Akses aplikasi
- Buka browser dan akses `http://localhost/nama-folder`
- Login default admin:
  - Username: admin
  - Password: [sesuaikan dengan password yang di-hash]

## 👥 Role Pengguna

### Admin
- Manajemen user (guru dan siswa)
- Manajemen kelas
- Manajemen mata pelajaran

### Guru
- Upload materi pembelajaran
- Membuat dan mengelola kuis
- Melihat progress dan nilai siswa

### Siswa
- Akses materi pembelajaran
- Mengerjakan kuis
- Melihat progress belajar dan nilai

## 🔐 Keamanan

- Password di-hash menggunakan algoritma bcrypt
- Validasi input untuk mencegah SQL injection
- Autentikasi dan autorisasi berbasis role
- Proteksi terhadap file upload

## 📝 Lisensi

[Soon]

## 📧 Kontak

Untuk pertanyaan dan dukungan, silakan hubungi:
[soon]