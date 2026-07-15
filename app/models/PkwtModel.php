<?php
class PkwtModel extends Model {
    protected $table = 'pkwt';
    protected $primaryKey = 'id_pkwt';
    
    public function getMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '', $limit = 15, $offset = 0) {
    $sql = "SELECT p.*, c.nama, c.posisi, r.kode_rig,
            DATEDIFF(p.tanggal_berakhir, CURDATE()) as sisa_hari
            FROM pkwt p
            JOIN crew c ON p.id_crew = c.id_crew
            JOIN rig r ON c.id_rig = r.id_rig
            WHERE p.id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p.id_crew)";
    if (!$isAllRig && !empty($rigIds)) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
    if (!empty($filter_rig)) $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
    if (!empty($filter_status)) {
        if ($filter_status == 'exp') $sql .= " AND p.tanggal_berakhir < CURDATE()";
        elseif ($filter_status == 'soon') $sql .= " AND p.tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        elseif ($filter_status == 'ok') $sql .= " AND p.tanggal_berakhir > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    }
    if (!empty($filter_search)) $sql .= " AND c.nama LIKE '%" . $this->escape($filter_search) . "%'";
    $sql .= " ORDER BY p.tanggal_berakhir ASC LIMIT {$limit} OFFSET {$offset}";
    return $this->query($sql);
}

    public function countMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '') {
        $sql = "SELECT COUNT(*) as total FROM pkwt p
                JOIN crew c ON p.id_crew = c.id_crew
                JOIN rig r ON c.id_rig = r.id_rig
                WHERE p.id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p.id_crew)";
        if (!$isAllRig && !empty($rigIds)) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        if (!empty($filter_rig)) $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') $sql .= " AND p.tanggal_berakhir < CURDATE()";
            elseif ($filter_status == 'soon') $sql .= " AND p.tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            elseif ($filter_status == 'ok') $sql .= " AND p.tanggal_berakhir > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }
        if (!empty($filter_search)) $sql .= " AND c.nama LIKE '%" . $this->escape($filter_search) . "%'";
        return $this->query($sql)->fetch_assoc()['total'];
    }

        public function countActive($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM pkwt p 
                JOIN crew c ON p.id_crew = c.id_crew 
                WHERE p.id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p.id_crew)
                AND p.tanggal_berakhir > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countSoon($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM pkwt p 
                JOIN crew c ON p.id_crew = c.id_crew 
                WHERE p.id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p.id_crew)
                AND p.tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countExpired($rigIds, $isAllRig) {
        $sql = "SELECT COUNT(*) as total FROM pkwt p 
                JOIN crew c ON p.id_crew = c.id_crew 
                WHERE p.id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p.id_crew)
                AND p.tanggal_berakhir < CURDATE()";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function getByCrew($id_crew, $orderBy = 'id_badge', $order = 'DESC') {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_crew = ? ORDER BY {$orderBy} {$order}");
    $stmt->bind_param('i', $id_crew);
    $stmt->execute();
    return $stmt->get_result();
    }
}