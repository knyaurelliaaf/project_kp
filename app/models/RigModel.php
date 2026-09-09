<?php
class RigModel extends Model {
    protected $table = 'rig';
    protected $primaryKey = 'id_rig';
    
    // Rig utama untuk Crew Compliance Monitoring (GW-336 s/d GW-339)
    public function allActive() {
        $res = $this->query("SELECT * FROM {$this->table} WHERE status = 'aktif' AND kode_rig IN ('GW-336', 'GW-337', 'GW-338', 'GW-339') ORDER BY id_rig ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Seluruh rig termasuk rig khusus payroll
    public function allActiveIncludePayroll() {
        $res = $this->query("SELECT * FROM {$this->table} WHERE status = 'aktif' ORDER BY id_rig ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getRigsByIds($rigIds) {
        if (empty($rigIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($rigIds), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE id_rig IN ({$placeholders})";
        $res = $this->query($sql, $rigIds);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function findByKode($kode) {
        if (empty($kode)) return null;
        $clean = trim($kode);
        $res = $this->query("SELECT * FROM {$this->table} WHERE LOWER(TRIM(kode_rig)) = LOWER('{$this->db->real_escape_string($clean)}') LIMIT 1");
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }
}
