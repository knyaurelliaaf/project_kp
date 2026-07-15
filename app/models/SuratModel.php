<?php
class SuratModel extends Model {
    protected $table = 'surat';
    protected $primaryKey = 'id_surat';
    
    public function countByRig($id_rig, $isSuperAdmin) {
        if ($isSuperAdmin) {
            return $this->count();
        }
        return $this->count('id_rig', $id_rig);
    }
    
    public function getAllWithDetails($id_rig, $isSuperAdmin) {
        $sql = "SELECT s.*, r.kode_rig, j.kode as kode_jenis, j.nama_surat, u.nama as pembuat
                FROM surat s
                JOIN rig r ON s.id_rig = r.id_rig
                JOIN jenis_surat j ON s.id_jenis = j.id_jenis
                JOIN user u ON s.id_user = u.id_user";
        
        if (!$isSuperAdmin) {
            $sql .= " WHERE s.id_rig = " . intval($id_rig);
        }
        
        $sql .= " ORDER BY s.created_at DESC";
        
        return $this->query($sql);
    }
    
    public function getLastNumber() {
    $result = $this->query("SELECT MAX(no_urut) as last_no FROM surat");
    $row = $result->fetch_assoc();
    return $row['last_no'] ?? 0;
}
    public function getFiltered($rigIds, $isAllRig, $filter_rig = '', $filter_jenis = '', $filter_tahun = '', $filter_search = '', $limit = 10, $offset = 0) {
    $sql = "SELECT s.*, r.kode_rig, j.kode as kode_jenis, j.nama_surat, u.nama as pembuat
            FROM surat s
            JOIN rig r ON s.id_rig = r.id_rig
            JOIN jenis_surat j ON s.id_jenis = j.id_jenis
            JOIN user u ON s.id_user = u.id_user
            WHERE 1=1";
    
    if (!$isAllRig && !empty($rigIds)) {
        $sql .= " AND s.id_rig IN (" . implode(',', $rigIds) . ")";
    }
    if (!empty($filter_rig)) {
        $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
    }
    if (!empty($filter_jenis)) {
        $sql .= " AND j.kode = '" . $this->escape($filter_jenis) . "'";
    }
    if (!empty($filter_tahun)) {
        $sql .= " AND s.tahun = " . intval($filter_tahun);
    }
    if (!empty($filter_search)) {
        $s = $this->escape($filter_search);
        $sql .= " AND (s.nomor_surat LIKE '%{$s}%' OR s.keterangan LIKE '%{$s}%')";
    }
    
    $sql .= " ORDER BY s.created_at DESC LIMIT {$limit} OFFSET {$offset}";
    return $this->query($sql);
}

    public function getById($id) {
        $sql = "SELECT s.*, r.kode_rig, j.kode as kode_jenis, j.nama_surat
                FROM surat s
                JOIN rig r ON s.id_rig = r.id_rig
                JOIN jenis_surat j ON s.id_jenis = j.id_jenis
                WHERE s.id_surat = ?";
        return $this->query($sql, [$id])->fetch_assoc();
    }

    public function countFiltered($rigIds, $isAllRig, $filter_rig = '', $filter_jenis = '', $filter_tahun = '', $filter_search = '') {
    $sql = "SELECT COUNT(*) as total FROM surat s
            JOIN rig r ON s.id_rig = r.id_rig
            JOIN jenis_surat j ON s.id_jenis = j.id_jenis
            WHERE 1=1";
    
    if (!$isAllRig && !empty($rigIds)) {
        $sql .= " AND s.id_rig IN (" . implode(',', $rigIds) . ")";
    }
    if (!empty($filter_rig)) {
        $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
    }
    if (!empty($filter_jenis)) {
        $sql .= " AND j.kode = '" . $this->escape($filter_jenis) . "'";
    }
    if (!empty($filter_tahun)) {
        $sql .= " AND s.tahun = " . intval($filter_tahun);
    }
    if (!empty($filter_search)) {
        $s = $this->escape($filter_search);
        $sql .= " AND (s.nomor_surat LIKE '%{$s}%' OR s.keterangan LIKE '%{$s}%')";
    }
    
    return $this->query($sql)->fetch_assoc()['total'];
}

}