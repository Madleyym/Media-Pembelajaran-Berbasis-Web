<?php
class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function register($data)
    {
        try {
            // Mulai transaction
            $this->db->beginTransaction();

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert ke tabel users
            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, nama_lengkap, email, role, foto_profile) 
                VALUES (:username, :password, :nama_lengkap, :email, :role, :foto_profile)
            ");

            $stmt->execute([
                ':username' => $data['username'],
                ':password' => $hashedPassword,
                ':nama_lengkap' => $data['nama_lengkap'],
                ':email' => $data['email'],
                ':role' => 'siswa',
                ':foto_profile' => $data['foto_path']
            ]);

            $userId = $this->db->lastInsertId();

            // Insert ke tabel siswa_kelas
            $stmt = $this->db->prepare("
                INSERT INTO siswa_kelas (id_siswa, id_kelas, tahun_ajaran)
                VALUES (:id_siswa, :id_kelas, :tahun_ajaran)
            ");

            $stmt->execute([
                ':id_siswa' => $userId,
                ':id_kelas' => $data['id_kelas'],
                ':tahun_ajaran' => $data['tahun_ajaran']
            ]);

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback jika terjadi error
            $this->db->rollBack();
            throw $e;
        }
    }

    public function checkUsername($username)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    public function checkEmail($email)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
}
