<?php
class McuModel extends Model {
    protected $table = 'mcu';
    protected $primaryKey = 'id_mcu';
    
    public function countExpired($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM mcu m 
                JOIN crew c ON m.id_crew = c.id_crew 
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'
                AND m.expired < CURDATE()";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }
    
    public function countSoon($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM mcu m 
                JOIN crew c ON m.id_crew = c.id_crew 
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'
                AND m.expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }
    
    public function countActive($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM mcu m 
                JOIN crew c ON m.id_crew = c.id_crew 
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'
                AND m.expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }
    
    public function countExpiredByRig($id_rig) {
        $sql = "SELECT COUNT(*) as total FROM mcu m 
                JOIN crew c ON m.id_crew = c.id_crew 
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'
                AND m.expired < CURDATE() AND c.id_rig = " . intval($id_rig);
        return $this->query($sql)->fetch_assoc()['total'];
    }
    
    public function countSoonByRig($id_rig) {
        $sql = "SELECT COUNT(*) as total FROM mcu m 
                JOIN crew c ON m.id_crew = c.id_crew 
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'
                AND m.expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                AND c.id_rig = " . intval($id_rig);
        return $this->query($sql)->fetch_assoc()['total'];
    }
    
    public function getByCrew($id_crew, $orderBy = 'id_mcu', $order = 'DESC') {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_crew = ? ORDER BY {$orderBy} {$order}");
        $stmt->bind_param('i', $id_crew);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function getMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '', $limit = 15, $offset = 0) {
        $sql = "SELECT m.*, c.nama, c.posisi, r.kode_rig,
                DATEDIFF(m.expired, CURDATE()) as sisa_hari
                FROM mcu m
                JOIN crew c ON m.id_crew = c.id_crew
                JOIN rig r ON c.id_rig = r.id_rig
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'";
        
        if (!$isAllRig && !empty($rigIds)) {
            $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        }
        if (!empty($filter_rig)) {
            $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        }
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') $sql .= " AND m.expired < CURDATE()";
            elseif ($filter_status == 'soon') $sql .= " AND m.expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            elseif ($filter_status == 'ok') $sql .= " AND m.expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }
        if (!empty($filter_search)) {
            $s = $this->escape($filter_search);
            $sql .= " AND c.nama LIKE '%{$s}%'";
        }
        $sql .= " ORDER BY m.expired ASC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql);
    }

    public function countMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '') {
        $sql = "SELECT COUNT(*) as total FROM mcu m
                JOIN crew c ON m.id_crew = c.id_crew
                JOIN rig r ON c.id_rig = r.id_rig
                WHERE m.id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)
                AND c.status_aktif = 'aktif'";
        if (!$isAllRig && !empty($rigIds)) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        if (!empty($filter_rig)) $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') $sql .= " AND m.expired < CURDATE()";
            elseif ($filter_status == 'soon') $sql .= " AND m.expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            elseif ($filter_status == 'ok') $sql .= " AND m.expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }
        if (!empty($filter_search)) $sql .= " AND c.nama LIKE '%" . $this->escape($filter_search) . "%'";
        return $this->query($sql)->fetch_assoc()['total'];
    }
}
