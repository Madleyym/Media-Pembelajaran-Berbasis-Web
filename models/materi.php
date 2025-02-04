// models/Materi.php
<?php
class Materi
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getMateriByMapel($id_mapel)
    {
        $query = "SELECT m.*, mp.nama_mapel, u.nama_lengkap as nama_guru 
                 FROM materi m 
                 JOIN mata_pelajaran mp ON m.id_mapel = mp.id 
                 JOIN users u ON m.id_guru = u.id 
                 WHERE m.id_mapel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_mapel]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMateri($data, $file)
    {
        $allowed_types = ['video/mp4', 'audio/mpeg', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            return false;
        }

        // Proses upload file
        $target_dir = "uploads/materi/";
        $file_name = time() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $target_dir . $file_name);

        $query = "INSERT INTO materi (judul, deskripsi, id_mapel, id_guru, tipe_materi, file_materi) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['judul'],
            $data['deskripsi'],
            $data['id_mapel'],
            $data['id_guru'],
            $data['tipe_materi'],
            $file_name
        ]);
    }
}
?>