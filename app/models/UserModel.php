<?php
class UserModel extends Model {
    protected $table = 'user';
    protected $primaryKey = 'id_user';
    
    public function findByEmail($email) {
        $sql = "SELECT u.*, GROUP_CONCAT(r.kode_rig) as kode_rig_list
                FROM {$this->table} u 
                LEFT JOIN user_rig ur ON u.id_user = ur.id_user
                LEFT JOIN rig r ON ur.id_rig = r.id_rig
                WHERE u.email = ?
                GROUP BY u.id_user";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function all($orderBy = null, $order = 'ASC') {
    $sql = "SELECT u.*, GROUP_CONCAT(r.kode_rig) as rig_list
            FROM {$this->table} u 
            LEFT JOIN user_rig ur ON u.id_user = ur.id_user
            LEFT JOIN rig r ON ur.id_rig = r.id_rig
            GROUP BY u.id_user
            ORDER BY u.created_at DESC";
    return $this->query($sql);
}
    
    public function find($id) {
        $sql = "SELECT u.*, GROUP_CONCAT(r.kode_rig) as kode_rig_list
                FROM {$this->table} u 
                LEFT JOIN user_rig ur ON u.id_user = ur.id_user
                LEFT JOIN rig r ON ur.id_rig = r.id_rig
                WHERE u.id_user = ?
                GROUP BY u.id_user";
        return $this->query($sql, [$id])->fetch_assoc();
    }
    
    // Ambil daftar id_rig untuk user tertentu
    public function getRigIds($id_user) {
        $sql = "SELECT id_rig FROM user_rig WHERE id_user = ?";
        $result = $this->query($sql, [$id_user]);
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id_rig'];
        }
        return $ids;
    }
    
    // Simpan rig untuk user
    public function saveUserRigs($id_user, $rigIds) {
        // Hapus  yang lama
        $this->execute("DELETE FROM user_rig WHERE id_user = ?", [$id_user]);
        
        // Insert  baru
        foreach ($rigIds as $id_rig) {
            $this->execute("INSERT INTO user_rig (id_user, id_rig) VALUES (?, ?)", [$id_user, $id_rig]);
        }
    }
}