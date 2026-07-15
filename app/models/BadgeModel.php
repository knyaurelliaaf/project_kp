<?php
class BadgeModel extends Model {
    protected $table = 'badge';
    protected $primaryKey = 'id_badge';
    
        public function getMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '', $limit = 15, $offset = 0) {
        $sql = "SELECT b.*, c.nama, c.posisi, r.kode_rig,
                DATEDIFF(b.tanggal_expired, CURDATE()) as sisa_hari
                FROM badge b
                JOIN crew c ON b.id_crew = c.id_crew
                JOIN rig r ON c.id_rig = r.id_rig
                WHERE b.id_badge = (
                    SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b.id_crew
                )";
        
        if (!$isAllRig && !empty($rigIds)) {
            $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        }
        if (!empty($filter_rig)) {
            $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        }
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') {
                $sql .= " AND b.tanggal_expired < CURDATE()";
            } elseif ($filter_status == 'soon') {
                $sql .= " AND b.tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            } elseif ($filter_status == 'ok') {
                $sql .= " AND b.tanggal_expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            }
        }
        if (!empty($filter_search)) {
            $s = $this->escape($filter_search);
            $sql .= " AND (c.nama LIKE '%{$s}%' OR b.nomor_badge LIKE '%{$s}%')";
        }
        
        $sql .= " ORDER BY b.tanggal_expired ASC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql);
    }

    public function countMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '') {
        $sql = "SELECT COUNT(*) as total
                FROM badge b
                JOIN crew c ON b.id_crew = c.id_crew
                JOIN rig r ON c.id_rig = r.id_rig
                WHERE b.id_badge = (
                    SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b.id_crew
                )";
        
        if (!$isAllRig && !empty($rigIds)) {
            $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        }
        if (!empty($filter_rig)) {
            $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        }
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') {
                $sql .= " AND b.tanggal_expired < CURDATE()";
            } elseif ($filter_status == 'soon') {
                $sql .= " AND b.tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            } elseif ($filter_status == 'ok') {
                $sql .= " AND b.tanggal_expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            }
        }
        if (!empty($filter_search)) {
            $s = $this->escape($filter_search);
            $sql .= " AND (c.nama LIKE '%{$s}%' OR b.nomor_badge LIKE '%{$s}%')";
        }
        
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countActive($rigIds, $isAllRig) {
    $sql = "SELECT COUNT(*) as total FROM badge b 
            JOIN crew c ON b.id_crew = c.id_crew 
            WHERE b.id_badge = (SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b.id_crew)
            AND b.tanggal_expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
    return $this->query($sql)->fetch_assoc()['total'];
}

    public function countSoon($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM badge b 
                JOIN crew c ON b.id_crew = c.id_crew 
                WHERE b.id_badge = (SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b.id_crew)
                AND b.tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countExpired($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM badge b 
                JOIN crew c ON b.id_crew = c.id_crew 
                WHERE b.id_badge = (SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b.id_crew)
                AND b.tanggal_expired < CURDATE()";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function getLastBadge($id_crew) {
        $sql = "SELECT * FROM badge WHERE id_crew = ? ORDER BY id_badge DESC LIMIT 1";
        return $this->query($sql, [$id_crew])->fetch_assoc();
    }

    public function getByCrew($id_crew, $orderBy = 'id_badge', $order = 'DESC') {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_crew = ? ORDER BY {$orderBy} {$order}");
    $stmt->bind_param('i', $id_crew);
    $stmt->execute();
    return $stmt->get_result();
    }
}